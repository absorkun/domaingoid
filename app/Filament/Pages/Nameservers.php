<?php

namespace App\Filament\Pages;

use App\Models\Domain;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Nameservers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum |string |null $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Nameservers';

    protected string $view = 'filament.pages.nameservers';

    public array $ipResults = [];

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getData())
            ->columns([
                TextColumn::make('domain')
                    ->label('Domain')
                    ->getStateUsing(fn ($record) => $record->name . $record->zone)
                    ->searchable(),

                TextColumn::make('ns1')
                    ->label('NS1')
                    ->getStateUsing(fn ($record) => $record->domain_name_server[0] ?? '-'),

                TextColumn::make('ns2')
                    ->label('NS2')
                    ->getStateUsing(fn ($record) => $record->domain_name_server[1] ?? '-'),

                TextColumn::make('ip')
                    ->label('IP Address')
                    ->getStateUsing(function ($record) {
                        return $this->ipResults[$record->id] ?? '-';
                    }),
            ])
            ->actions([
                Action::make('get_ip')
                    ->label('Get IP')
                    ->action(function ($record) {
                        $ns = $record->domain_name_server[0] ?? null;

                        if (!$ns) {
                            $this->ipResults[$record->id] = 'NS kosong';
                            return;
                        }

                        $ips = gethostbynamel($ns);

                        $this->ipResults[$record->id] =
                            $ips ? implode(', ', $ips) : 'resolve failed';
                    })
            ])
            ->bulkActions([
                BulkAction::make('bulk_get_ip')
                    ->label('Bulk Get IP')
                    ->icon('heroicon-o-magnifying-glass')
                    ->action(function (Collection $records) {
                        foreach ($records as $record) {
                            $ns = $record->domain_name_server[0] ?? null;

                            if (!$ns) {
                                $this->ipResults[$record->id] = 'NS kosong';
                                continue;
                            }

                            $ips = gethostbynamel($ns);

                            $this->ipResults[$record->id] =
                                $ips ? implode(', ', $ips) : 'resolve failed';
                        }
                    })
            ])
            ->defaultPaginationPageOption(10);
    }

    /** @return Builder */
    protected function getData(): Builder|callable|null
    {
        /** @var Builder $query */
        $query = Domain::query()
            ->select(['id', 'name', 'zone', 'domain_name_server'])
            ->whereNotNull('domain_name_server')
            ->whereJsonDoesntContain('domain_name_server', 'ns-expired.domain.go.id');

        return $query;
    }
}
