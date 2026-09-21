<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('field_teams', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Organisasi pemilik tim
            |--------------------------------------------------------------------------
            | Contoh:
            | Manggala Agni
            | BPBD
            | Tim Rescue
            |--------------------------------------------------------------------------
            */

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Informasi tim
            |--------------------------------------------------------------------------
            */

            $table->string('team_name');


            $table->string('leader_name')
                ->nullable();


            $table->string('phone',20)
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Status tim
            |--------------------------------------------------------------------------
            */

            $table->enum('status',[
                'active',
                'inactive'
            ])
            ->default('active');


            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('field_teams');
    }

};
