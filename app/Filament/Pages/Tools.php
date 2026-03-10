<?php

namespace App\Filament\Pages;

use App\Jobs\ExportNameserverIps;
use App\Models\Domain;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class Tools extends Page
{
    protected string $view = 'filament.pages.tools';
    
    public function generateCsv()
    {
        ExportNameserverIps::dispatch(auth()->id());
    }
}
