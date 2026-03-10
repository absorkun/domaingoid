<?php

namespace App\Jobs;

use App\Models\Domain;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportNameserverIps implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Batas retry job jika terjadi exception fatal (bukan retry internet).
     */
    public int $tries = 3;

    /**
     * Maksimal waktu eksekusi job dalam detik (10 menit).
     */
    public int $timeout = 600;

    private const DNS_MAX_ATTEMPTS    = 5;
    private const DNS_RETRY_DELAY_SEC = 3;
    private const NET_CHECK_HOST      = 'dns.google';
    private const NET_WAIT_SEC        = 10;
    private const NET_MAX_WAIT_SEC    = 300; // Tunggu internet maksimal 5 menit

    public function __construct(public int $userId)
    {
        //
    }

    public function handle(): void
    {
        $filename = 'exports/ns-ip-export-' . now()->format('Ymd-His') . '.csv';
        $path     = Storage::disk('public')->path($filename);

        // Pastikan direktori tersedia
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $handle = fopen($path, 'w');

        fputcsv($handle, ['Domain', 'NS1', 'IP']);

        $processed = 0;
        $limit     = 1_000_000; // Tentukan limit naris domain yang diinginkan

        // Gunakan cursor() + manual limit agar take() + chunk() tidak bentrok
        Domain::query()
            ->select(['name', 'zone', 'domain_name_server'])
            ->whereNotNull('domain_name_server')
            ->whereJsonDoesntContain('domain_name_server', 'ns-expired.domain.go.id')
            ->chunk(100, function ($domains) use ($handle, &$processed, $limit) {

                foreach ($domains as $domain) {

                    if ($processed >= $limit) {
                        return false; // Hentikan chunk
                    }

                    $domainName = $domain->name . $domain->zone;
                    $ns         = $domain->domain_name_server[0] ?? null;

                    if (! $ns) {
                        fputcsv($handle, [$domainName, '-', 'NS kosong']);
                        $processed++;
                        continue;
                    }

                    $result = $this->resolveWithRetry($ns);

                    fputcsv($handle, [$domainName, $ns, $result]);
                    $processed++;
                }
            });

        fclose($handle);

        $url  = Storage::disk('public')->url($filename);
        $user = \App\Models\User::find($this->userId);

        Notification::make()
            ->title('Export IP address dari Nameserver telah selesai')
            ->actions([
                Action::make('download')
                    ->label('Download CSV')
                    ->url($url),
            ])
            ->success()
            ->sendToDatabase($user);
    }

    /**
     * Resolve hostname dengan retry otomatis.
     * - Jika resolve gagal beberapa kali berturut-turut, diasumsikan internet putus.
     * - Job akan menunggu (blocking sleep) sampai koneksi pulih, lalu lanjut.
     */
    private function resolveWithRetry(string $ns): string
    {
        $attempt = 0;

        while (true) {
            $ips = gethostbynamel($ns);

            if ($ips !== false) {
                return $ips[0]; // Sukses
            }

            $attempt++;

            if ($attempt >= self::DNS_MAX_ATTEMPTS) {
                // Setelah beberapa kali gagal, cek apakah internet memang putus
                if (! $this->isInternetAvailable()) {
                    $this->waitForInternet();
                    $attempt = 0; // Reset counter setelah internet kembali
                    continue;
                }

                // Internet tersedia tapi NS memang tidak bisa di-resolve
                return 'resolve failed';
            }

            sleep(self::DNS_RETRY_DELAY_SEC);
        }
    }

    /**
     * Cek ketersediaan internet dengan mencoba resolve host stabil (misal: dns.google).
     */
    private function isInternetAvailable(): bool
    {
        return gethostbynamel(self::NET_CHECK_HOST) !== false;
    }

    /**
     * Tunggu sampai internet tersedia kembali, dengan batas waktu maksimal.
     * Jika melewati batas, lempar exception agar job bisa di-retry oleh queue worker.
     *
     * @throws \RuntimeException
     */
    private function waitForInternet(): void
    {
        $waited = 0;

        while (! $this->isInternetAvailable()) {
            if ($waited >= self::NET_MAX_WAIT_SEC) {
                throw new \RuntimeException(
                    "Koneksi internet tidak tersedia setelah menunggu " . self::NET_MAX_WAIT_SEC . " detik. Job akan di-retry."
                );
            }

            sleep(self::NET_WAIT_SEC);
            $waited += self::NET_WAIT_SEC;
        }
    }
}