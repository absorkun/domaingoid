<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $fillable = [
        'name',
        'zone',
        'registered_at',
        'expires_at',
        'note',
        'domain_name_server',
        'ns_country',
        'doc_domain_1',
        'doc_domain_2',
        'doc_domain_3',
        'doc_domain_4',
        'doc_domain_5',
        'fax',
        'name_organization',
        'klasifikasi_instansi_id',
        'nama_instansi',
        'address',
        'postal_code',
        'status',
        'status_stage',
        'province_id',
        'city_id',
        'district_id',
        'village_id',
        'registrant_id',
        'phone',
        'product_id',
        'approved_by_id',
        'canceled_by_id',
        'description_domain',
        'approved_payment_by_id',
        'type_domain',
        'renewal_date',
        'active_date',
        'expired_date',
        'duration',
        'd_registrant_type',
        'ammount',
        'd_status',
        'd_xname',
        'u_organization_type',
        'u_organization_name',
        'u_state',
        'u_city',
        'u_street2',
        'u_street3',
        'deleted_by_id',
        'processed_update_ns_at'
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'expires_at' => 'datetime',
        'renewal_date' => 'date',
        'active_date' => 'date','expired_date' => 'date',
        'processed_update_ns_at' => 'datetime',
        'domain_name_server' => 'array',
        'ns_country' => 'array',
    ];

    public function getDomainAttribute()
    {
        return $this->name . $this->zone;
    }

}
