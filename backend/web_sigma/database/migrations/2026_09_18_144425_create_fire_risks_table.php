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
        Schema::create('fire_risks', function (Blueprint $table) {

            $table->id();


            $table->foreignId('region_id')
                ->constrained()
                ->cascadeOnDelete();


            $table->integer('risk_score');


            $table->enum('risk_level', [
                'low',
                'medium',
                'high',
                'extreme'
            ]);


            $table->decimal('temperature', 8, 2)
                ->nullable();


            $table->decimal('humidity', 8, 2)
                ->nullable();


            $table->decimal('rainfall', 8, 2)
                ->nullable();


            $table->decimal('wind_speed', 8, 2)
                ->nullable();


            $table->decimal('vegetation_index', 8, 4)
                ->nullable();


            $table->timestamp('calculated_at');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fire_risks');
    }
};
