<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('system_configurations', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Nama konfigurasi
            |--------------------------------------------------------------------------
            */

            $table->string('key',100)
                ->unique();



            /*
            |--------------------------------------------------------------------------
            | Nilai konfigurasi
            |--------------------------------------------------------------------------
            */

            $table->text('value')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Tipe data konfigurasi
            |--------------------------------------------------------------------------
            */

            $table->enum('type',[
                'string',
                'integer',
                'boolean'
            ])
            ->default('string');



            /*
            |--------------------------------------------------------------------------
            | Penjelasan konfigurasi
            |--------------------------------------------------------------------------
            */

            $table->text('description')
                ->nullable();



            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('system_configurations');
    }

};
