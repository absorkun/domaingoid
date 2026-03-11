<?php

namespace App\Filament\Pages;

use App\Jobs\ExportNameserverIps;
use App\Models\Domain;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;

class Tools extends Page
{
    protected string $view = 'filament.pages.tools';

    protected static BackedEnum | string | null $navivationIcon = 'heroicon-o-wrench-screwdriver';
    
    public function generateCsv()
    {
        ExportNameserverIps::dispatch(auth()->id());
    }
}
