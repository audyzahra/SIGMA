<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | User yang melakukan aksi
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Jenis aksi
            |--------------------------------------------------------------------------
            */

            $table->string('action',100);



            /*
            |--------------------------------------------------------------------------
            | Modul sistem
            |--------------------------------------------------------------------------
            | Contoh:
            | users
            | incidents
            | organizations
            | ai_models
            |--------------------------------------------------------------------------
            */

            $table->string('module',100);



            /*
            |--------------------------------------------------------------------------
            | Detail aktivitas
            |--------------------------------------------------------------------------
            */

            $table->text('description')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Data sebelum perubahan
            |--------------------------------------------------------------------------
            */

            $table->json('old_values')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Data setelah perubahan
            |--------------------------------------------------------------------------
            */

            $table->json('new_values')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Informasi pengguna
            |--------------------------------------------------------------------------
            */

            $table->string('ip_address',45)
                ->nullable();


            $table->text('user_agent')
                ->nullable();



            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }

};
