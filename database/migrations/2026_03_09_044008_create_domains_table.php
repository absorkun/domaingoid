<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('zone', 10);
            $table->datetime('registered_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->text('note')->nullable();
            $table->text('domain_name_server')->nullable();
            $table->text('ns_country')->nullable();
            $table->string('doc_domain_1', 100)->nullable();
            $table->string('doc_domain_2', 100)->nullable();
            $table->string('doc_domain_3', 100)->nullable();
            $table->string('doc_domain_4', 100)->nullable();
            $table->string('doc_domain_5', 100)->nullable();
            $table->string('fax', 20)->nullable();
            $table->string('name_organization', 100);
            $table->integer('klasifikasi_instansi_id')->nullable();
            $table->string('nama_instansi', 100);
            $table->text('address')->nullable();
            $table->char('postal_code', 10)->nullable();
            $table->enum('status', [
                'error',
                'draft',
                'verifikasi dokumen',
                'pending payment',
                'verifikasi pembayaran',
                'active',
                'suspend',
                'cancelled',
                'reject',
            ])->default('draft');
            $table->enum('status_stage', [
                'information-instansi',
                'information-domain',
                'document-domain',
                'preview',
            ]);
            $table->integer('province_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->integer('village_id')->nullable();
            $table->integer('registrant_id');
            $table->string('phone', 20)->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('approved_by_id')->nullable();
            $table->integer('canceled_by_id')->nullable();
            $table->string('description_domain')->nullable();
            $table->integer('approved_payment_by_id')->nullable();
            $table->enum('type_domain', [
                'registration',
                'renewal',
                'transfer'
            ])->default('registration');
            $table->date('renewal_date')->nullable();
            $table->date('active_date')->nullable();
            $table->date('expired_date')->nullable();
            $table->tinyInteger('duration')->nullable();
            $table->string('d_registrant_type', 100)->nullable();
            $table->integer('ammount')->nullable();
            $table->char('d_status', 5)->nullable();
            $table->string('d_xname')->nullable();
            $table->string('u_organization_type', 100)->nullable();
            $table->string('u_organization_name')->nullable();
            $table->string('u_state', 50)->nullable();
            $table->string('u_city', 50)->nullable();
            $table->text('u_street2')->nullable();
            $table->text('u_street3')->nullable();
            $table->integer('deleted_by_id')->nullable();
            
            $table->timestamp('processed_update_ns_at')->nullable();
            
            $table->index(['name', 'zone']);
            $table->index('processed_update_ns_at');
            $table->index('registrant_id');
            $table->index('status');
            $table->index('expired_date');
            $table->unique(['name', 'zone']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
