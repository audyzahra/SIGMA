<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('voice_reports', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | User masyarakat
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Relasi laporan masyarakat
            |--------------------------------------------------------------------------
            | Bisa kosong jika voice report
            | belum menjadi citizen report
            |--------------------------------------------------------------------------
            */

            $table->foreignId('citizen_report_id')
                ->nullable()
                ->constrained('citizen_reports')
                ->nullOnDelete();



            /*
            |--------------------------------------------------------------------------
            | File audio
            |--------------------------------------------------------------------------
            */

            $table->string('audio_file')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Hasil Speech To Text
            |--------------------------------------------------------------------------
            */

            $table->text('transcript')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Lokasi hasil deteksi
            |--------------------------------------------------------------------------
            | Menggunakan geometry untuk GIS
            |--------------------------------------------------------------------------
            */

            $table->geometry('detected_location')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Status proses AI
            |--------------------------------------------------------------------------
            */

            $table->enum('processing_status',[
                'pending',
                'processed',
                'failed'
            ])
            ->default('pending');



            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('voice_reports');
    }

};
