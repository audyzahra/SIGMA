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
        Schema::create('hotspots', function (Blueprint $table) {

            $table->id();


            $table->foreignId('source_id')
                ->constrained('data_sources')
                ->cascadeOnDelete();


            $table->decimal('latitude', 10, 8);

            $table->decimal('longitude', 11, 8);


            $table->string('satellite_name')
                ->nullable();


            $table->decimal('brightness_temperature', 8, 2)
                ->nullable();


            $table->integer('confidence_level')
                ->nullable();


            $table->timestamp('detected_at');


            $table->enum('status', [
                'active',
                'resolved'
            ])
                ->default('active');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotspots');
    }
};
