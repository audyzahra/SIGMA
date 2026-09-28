<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize existing risk levels before changing the ENUM.
        DB::statement("
            UPDATE fire_risks
            SET risk_level = CASE LOWER(risk_level)
                WHEN 'low' THEN 'LOW'
                WHEN 'medium' THEN 'MEDIUM'
                WHEN 'high' THEN 'HIGH'
                WHEN 'extreme' THEN 'HIGH'
                ELSE 'HIGH'
            END
        ");

        Schema::table('fire_risks', function (Blueprint $table) {
            $table->enum('risk_level', [
                'LOW',
                'MEDIUM',
                'HIGH'
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('fire_risks', function (Blueprint $table) {
            $table->enum('risk_level', [
                'low',
                'medium',
                'high',
                'extreme'
            ])->change();
        });
    }
};
