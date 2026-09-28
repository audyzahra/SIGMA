<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fire_risks', function (Blueprint $table) {

            $table->decimal(
                'ai_confidence',
                5,
                2
            )
            ->nullable()
            ->after('risk_level');

        });
    }



    public function down(): void
    {
        Schema::table('fire_risks', function (Blueprint $table) {

            $table->dropColumn('ai_confidence');

        });
    }
};
