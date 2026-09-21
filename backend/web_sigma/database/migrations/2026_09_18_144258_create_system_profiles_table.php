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
        Schema::create('system_profiles', function (Blueprint $table) {

            $table->id();

            $table->string('title');

            $table->text('tagline')
                ->nullable();


            $table->string('hero_image')
                ->nullable();


            $table->string('total_hotspot_label')
                ->nullable();


            $table->string('total_incident_label')
                ->nullable();


            $table->string('contact_email')
                ->nullable();


            $table->string('contact_phone', 20)
                ->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_profiles');
    }
};
