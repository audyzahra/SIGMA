<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('citizen_reports', function (Blueprint $table) {

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | User masyarakat yang membuat laporan
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Relasi incident
            |--------------------------------------------------------------------------
            | Bisa null karena laporan baru belum tentu
            | langsung menjadi incident
            |--------------------------------------------------------------------------
            */

            $table->foreignId('incident_id')
                ->nullable()
                ->constrained('incidents')
                ->nullOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Jenis laporan
            |--------------------------------------------------------------------------
            */

            $table->enum('report_type', [
                'fire',
                'smoke',
                'burning_activity',
                'other'
            ]);



            /*
            |--------------------------------------------------------------------------
            | Lokasi laporan
            |--------------------------------------------------------------------------
            */

            $table->decimal('latitude',10,8);

            $table->decimal('longitude',11,8);



            /*
            |--------------------------------------------------------------------------
            | Multimedia laporan
            |--------------------------------------------------------------------------
            */

            $table->string('photo')
                ->nullable();


            $table->string('video')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Deskripsi laporan
            |--------------------------------------------------------------------------
            */

            $table->text('description')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Status verifikasi
            |--------------------------------------------------------------------------
            */

            $table->enum('verification_status',[
                'pending',
                'verified',
                'rejected'
            ])
            ->default('pending');


            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('citizen_reports');
    }

};
