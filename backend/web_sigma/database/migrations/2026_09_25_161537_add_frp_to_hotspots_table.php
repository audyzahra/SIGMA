<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

    public function up(): void
    {
        Schema::table('hotspots', function (Blueprint $table) {

            $table->decimal(
                'frp',
                10,
                2
            )
            ->nullable()
            ->after('confidence_level');

        });
    }



    public function down(): void
    {
        Schema::table('hotspots', function (Blueprint $table) {

            $table->dropColumn('frp');

        });
    }

};
