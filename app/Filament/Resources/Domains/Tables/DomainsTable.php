<?php

namespace App\Filament\Resources\Domains\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DomainsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('zone')
                    ->searchable(),
                TextColumn::make('domain_name_server'),
                TextColumn::make('registered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('doc_domain_1')
                    ->searchable(),
                TextColumn::make('doc_domain_2')
                    ->searchable(),
                TextColumn::make('doc_domain_3')
                    ->searchable(),
                TextColumn::make('doc_domain_4')
                    ->searchable(),
                TextColumn::make('doc_domain_5')
                    ->searchable(),
                TextColumn::make('fax')
                    ->searchable(),
                TextColumn::make('klasifikasi_instansi_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nama_instansi')
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('status_stage')
                    ->badge(),
                TextColumn::make('province_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('city_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('district_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('village_id')
                    ->searchable(),
                TextColumn::make('registrant_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('product_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('approved_by_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('canceled_by_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('description_domain')
                    ->searchable(),
                TextColumn::make('approved_payment_by_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type_domain')
                    ->badge(),
                TextColumn::make('renewal_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('active_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expired_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('duration')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('d_registrant_type')
                    ->searchable(),
                TextColumn::make('ammount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('d_status')
                    ->searchable(),
                TextColumn::make('d_xname')
                    ->searchable(),
                TextColumn::make('u_organization_type')
                    ->searchable(),
                TextColumn::make('u_organization_name')
                    ->searchable(),
                TextColumn::make('u_state')
                    ->searchable(),
                TextColumn::make('u_city')
                    ->searchable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_by_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('processed_update_ns_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
