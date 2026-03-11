<?php

namespace App\Jobs;

use App\Models\Domain;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportNameserverIps implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Retry hanya untuk exception fatal (bukan DNS timeout biasa).
     * Jangan terlalu tinggi karena 1 run bisa sangat lama.
     */
    public int $tries = 2;

    /**
     * Timeout 2 jam: 50.000 domain × worst-case ~0.5 detik/domain = ~7 jam.
     * Sesuaikan juga stopTime di supervisord agar tidak di-kill paksa.
     * Di supervisord: stopwaitsecs=7200
     */
    public int $timeout = 7200;

    /**
     * Jangan retry jika terjadi exception runtime (internet mati melebihi batas).
     * Job akan masuk status "failed" dan bisa di-retry manual.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(5);
    }

    private const DNS_MAX_ATTEMPTS    = 3;
    private const DNS_RETRY_DELAY_SEC = 2;
    private const NET_CHECK_HOST      = 'dns.google';
    private const NET_WAIT_SEC        = 15;
    private const NET_MAX_WAIT_SEC    = 300;
    private const CHUNK_SIZE          = 200; // Lebih besar = lebih sedikit query DB

    public function __construct(public readonly int $userId)
    {
        //
    }

    public function handle(): void
    {
        $filename  = 'exports/ns-ip-export-' . now()->format('Ymd-His') . '.csv';
        $tmpPath   = Storage::disk('public')->path($filename . '.tmp');
        $finalPath = Storage::disk('public')->path($filename);

        $dir = dirname($finalPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Tulis ke file .tmp dulu, atomic rename di akhir.
        // Ini mencegah user mendownload file setengah-jadi jika job mati di tengah.
        $handle = fopen($tmpPath, 'w');
        if ($handle === false) {
            throw new \RuntimeException("Gagal membuka file untuk ditulis: {$tmpPath}");
        }

        fputcsv($handle, ['Domain', 'NS1', 'IP NS1']);

        $processed = 0;
        $failed    = 0;

        Log::info("[ExportNameserverIps] Job mulai untuk user #{$this->userId}");

        try {
            // chunkById() lebih aman dari chunk() untuk dataset besar:
            // - Tidak ada masalah offset/skip saat data berubah di tengah proses
            // - Query pakai WHERE id > ? yang diindeks, jauh lebih cepat dari OFFSET
            Domain::query()
                ->select(['id', 'name', 'zone', 'domain_name_server'])
                ->whereNotNull('domain_name_server')
                ->whereJsonDoesntContain('domain_name_server', 'ns-expired.domain.go.id')
                ->chunkById(self::CHUNK_SIZE, function ($domains) use ($handle, &$processed, &$failed) {

                    foreach ($domains as $domain) {
                        $domainName = $domain->name . $domain->zone;
                        $ns         = $domain->domain_name_server[0] ?? null;

                        if (! $ns) {
                            fputcsv($handle, [$domainName, '-', 'NS kosong']);
                            $processed++;
                            continue;
                        }

                        $result = $this->resolveWithRetry($ns);

                        if ($result === 'resolve failed') {
                            $failed++;
                        }

                        fputcsv($handle, [$domainName, $ns, $result]);
                        $processed++;
                    }

                    // Flush buffer ke disk setiap chunk agar tidak hilang jika crash
                    fflush($handle);

                    // Bebaskan memori PHP setelah setiap chunk
                    gc_collect_cycles();

                    Log::info("[ExportNameserverIps] Progress: {$processed} domain diproses.");
                });

        } finally {
            // Pastikan file selalu ditutup meski ada exception
            fclose($handle);
        }

        // Atomic rename: file .tmp → file final
        // Jika job mati sebelum ini, file .tmp akan ada tapi tidak terdaftar sebagai download
        rename($tmpPath, $finalPath);

        Log::info("[ExportNameserverIps] Selesai. Total: {$processed}, Gagal resolve: {$failed}");

        $url  = Storage::disk('public')->url($filename);
        $user = User::find($this->userId);

        if (! $user) {
            Log::warning("[ExportNameserverIps] User #{$this->userId} tidak ditemukan, notifikasi dilewati.");
            return;
        }

        Notification::make()
            ->title("Export Nameserver IP selesai ({$processed} domain, {$failed} gagal resolve)")
            ->actions([
                Action::make('download')
                    ->label('Download CSV')
                    ->url($url),
            ])
            ->success()
            ->sendToDatabase($user);
    }

    /**
     * Dipanggil otomatis oleh Laravel jika job melebihi $tries atau $timeout.
     * Kirim notifikasi gagal ke user agar tidak menunggu tanpa kabar.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("[ExportNameserverIps] Job gagal: " . $exception->getMessage());

        $user = User::find($this->userId);

        if ($user) {
            Notification::make()
                ->title('Export Nameserver IP gagal')
                ->body('Terjadi error: ' . $exception->getMessage())
                ->danger()
                ->sendToDatabase($user);
        }
    }

    /**
     * Resolve hostname dengan retry + deteksi internet putus.
     */
    private function resolveWithRetry(string $ns): string
    {
        $attempt = 0;

        while (true) {
            $ips = gethostbynamel($ns);

            if ($ips !== false) {
                return $ips[0];
            }

            $attempt++;

            if ($attempt >= self::DNS_MAX_ATTEMPTS) {
                if (! $this->isInternetAvailable()) {
                    $this->waitForInternet();
                    $attempt = 0;
                    continue;
                }

                return 'resolve failed';
            }

            sleep(self::DNS_RETRY_DELAY_SEC);
        }
    }

    private function isInternetAvailable(): bool
    {
        return gethostbynamel(self::NET_CHECK_HOST) !== false;
    }

    private function waitForInternet(): void
    {
        $waited = 0;
        Log::warning("[ExportNameserverIps] Internet tidak tersedia, menunggu...");

        while (! $this->isInternetAvailable()) {
            if ($waited >= self::NET_MAX_WAIT_SEC) {
                throw new \RuntimeException(
                    "Koneksi internet tidak tersedia setelah {$waited} detik. Job akan di-retry."
                );
            }

            sleep(self::NET_WAIT_SEC);
            $waited += self::NET_WAIT_SEC;
        }

        Log::info("[ExportNameserverIps] Internet tersedia kembali setelah {$waited} detik.");
    }
}