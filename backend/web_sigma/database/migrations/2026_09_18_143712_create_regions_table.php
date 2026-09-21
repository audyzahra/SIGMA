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
        Schema::create('regions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('regions')
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('code', 50)
                ->nullable();

            $table->enum('level', [
                'province',
                'regency',
                'district'
            ]);

            // PostGIS polygon
            $table->geometry('geometry')
                ->nullable();

            $table->decimal('area_size', 12, 2)
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
