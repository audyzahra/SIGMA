<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotspots', function (Blueprint $table) {
            $table->string('data_origin', 30)
                ->default('legacy');

            $table->string('record_key', 64)
                ->nullable()
                ->unique();
        });
    }

    public function down(): void
    {
        Schema::table('hotspots', function (Blueprint $table) {
            $table->dropUnique(['record_key']);
            $table->dropColumn(['data_origin', 'record_key']);
        });
    }
};
