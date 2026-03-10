<?php

namespace App\Filament\Resources\Domains\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DomainForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('zone')
                    ->required(),
                DateTimePicker::make('registered_at'),
                DateTimePicker::make('expires_at'),
                Textarea::make('note')
                    ->columnSpanFull(),
                Textarea::make('domain_name_server')
                    ->columnSpanFull(),
                Textarea::make('ns_country')
                    ->columnSpanFull(),
                TextInput::make('doc_domain_1'),
                TextInput::make('doc_domain_2'),
                TextInput::make('doc_domain_3'),
                TextInput::make('doc_domain_4'),
                TextInput::make('doc_domain_5'),
                TextInput::make('fax'),
                Textarea::make('name_organization')
                    ->columnSpanFull(),
                TextInput::make('klasifikasi_instansi_id')
                    ->numeric(),
                TextInput::make('nama_instansi'),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('postal_code'),
                Select::make('status')
                    ->options([
            'error' => 'Error',
            'draft' => 'Draft',
            'verifikasi dokumen' => 'Verifikasi dokumen',
            'pending payment' => 'Pending payment',
            'verifikasi pembayaran' => 'Verifikasi pembayaran',
            'active' => 'Active',
            'suspend' => 'Suspend',
            'cancelled' => 'Cancelled',
            'reject' => 'Reject',
        ])
                    ->default('draft')
                    ->required(),
                Select::make('status_stage')
                    ->options([
            'information-instansi' => 'Information instansi',
            'information-domain' => 'Information domain',
            'document-domain' => 'Document domain',
            'preview' => 'Preview',
        ])
                    ->default('information-instansi')
                    ->required(),
                TextInput::make('province_id')
                    ->numeric(),
                TextInput::make('city_id')
                    ->numeric(),
                TextInput::make('district_id')
                    ->numeric(),
                TextInput::make('village_id'),
                TextInput::make('registrant_id')
                    ->required()
                    ->numeric(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('product_id')
                    ->numeric(),
                TextInput::make('approved_by_id')
                    ->numeric(),
                TextInput::make('canceled_by_id')
                    ->numeric(),
                TextInput::make('description_domain'),
                TextInput::make('approved_payment_by_id')
                    ->numeric(),
                Select::make('type_domain')
                    ->options(['registration' => 'Registration', 'renewal' => 'Renewal', 'transfer' => 'Transfer'])
                    ->default('registration'),
                DatePicker::make('renewal_date'),
                DatePicker::make('active_date'),
                DatePicker::make('expired_date'),
                TextInput::make('duration')
                    ->numeric(),
                TextInput::make('d_registrant_type'),
                TextInput::make('ammount')
                    ->numeric(),
                TextInput::make('d_status'),
                TextInput::make('d_xname'),
                TextInput::make('u_organization_type'),
                TextInput::make('u_organization_name'),
                TextInput::make('u_state'),
                TextInput::make('u_city'),
                Textarea::make('u_street2')
                    ->columnSpanFull(),
                Textarea::make('u_street3')
                    ->columnSpanFull(),
                TextInput::make('deleted_by_id')
                    ->numeric(),
                DateTimePicker::make('processed_update_ns_at'),
            ]);
    }
}
