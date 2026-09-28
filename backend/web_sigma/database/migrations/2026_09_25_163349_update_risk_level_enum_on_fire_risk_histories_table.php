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
        Schema::table('fire_risk_histories', function (Blueprint $table) {

            $table->enum('risk_level', [

                'LOW',

                'MEDIUM',

                'HIGH'

            ])
            ->change();

        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fire_risk_histories', function (Blueprint $table) {

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
