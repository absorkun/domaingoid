<?php

namespace App\Filament\Widgets;

use App\Models\Domain;
use DB;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class DomainCounter extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $stats = Cache::remember('domain_stats', 60, function () {
            return Domain::query()
                ->select([
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(status = 'active') as active"),
                    DB::raw('SUM(expired_date < CURDATE()) as expired'),
                    DB::raw("
                        SUM(
                            JSON_EXTRACT(domain_name_server, '$[0]') IS NOT NULL
                            AND JSON_EXTRACT(domain_name_server, '$[0]') != '\"ns-expired.domain.go.id\"'
                        ) as ns
                    "),
                ])
                ->first();
        });

        return [
            Stat::make('Semua Domain', $stats->total),
            Stat::make('Domain Aktif', $stats->active),
            Stat::make('Domain Kadaluarsa', $stats->expired),
            Stat::make('Nameserver valid', $stats->ns),
        ];
    }
}
