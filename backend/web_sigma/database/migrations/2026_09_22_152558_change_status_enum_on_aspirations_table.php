<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aspirations', function (Blueprint $table) {

            $table->enum('status', [
                'pending',
                'done'
            ])
            ->default('pending')
            ->change();

        });
    }


    public function down(): void
    {
        Schema::table('aspirations', function (Blueprint $table) {

            $table->enum('status', [
                'pending',
                'processed',
                'completed',
                'rejected',
            ])
            ->default('pending')
            ->change();

        });
    }
};