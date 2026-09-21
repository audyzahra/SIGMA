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
        Schema::create('incidents', function (Blueprint $table) {

            $table->id();


            $table->enum('source_type', [
                'hotspot',
                'report',
                'manual'
            ]);


            $table->decimal('latitude', 10, 8);

            $table->decimal('longitude', 11, 8);


            $table->text('location_description')
                ->nullable();


            $table->enum('fire_status', [
                'detected',
                'on_process',
                'extinguished'
            ]);


            $table->enum('severity_level', [
                'low',
                'medium',
                'high'
            ]);


            $table->string('photo')
                ->nullable();


            $table->timestamp('detected_at')
                ->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
