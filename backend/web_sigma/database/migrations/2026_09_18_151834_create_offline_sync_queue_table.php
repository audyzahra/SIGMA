<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('offline_sync_queue', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Jenis data yang akan disinkronkan
            |--------------------------------------------------------------------------
            | Contoh:
            | incident_update
            | field_verification
            | navigation_log
            |--------------------------------------------------------------------------
            */

            $table->string('data_type',100);



            /*
            |--------------------------------------------------------------------------
            | Data sementara offline
            |--------------------------------------------------------------------------
            */

            $table->json('payload');



            /*
            |--------------------------------------------------------------------------
            | Status sinkronisasi
            |--------------------------------------------------------------------------
            */

            $table->enum('sync_status',[
                'pending',
                'synced'
            ])
            ->default('pending');



            /*
            |--------------------------------------------------------------------------
            | Identitas perangkat
            |--------------------------------------------------------------------------
            */

            $table->string('device_id')
                ->nullable();



            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('offline_sync_queue');
    }

};
