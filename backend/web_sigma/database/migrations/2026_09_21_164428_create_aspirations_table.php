<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aspirations', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('phone', 30);

            $table->string('email');

            $table->string('organization')->nullable();

            $table->string('region')->nullable();

            $table->text('description');

            $table->enum('status', [
                'pending',
                'processed',
                'completed',
                'rejected',
            ])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aspirations');
    }
};