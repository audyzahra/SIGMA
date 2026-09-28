<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

    public function up(): void
    {
        Schema::table('fire_risks', function (Blueprint $table) {

            $table->enum('risk_level', [
                'LOW',
                'MEDIUM',
                'HIGH'
            ])
            ->change();

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
            ])
            ->change();

        });
    }

};
