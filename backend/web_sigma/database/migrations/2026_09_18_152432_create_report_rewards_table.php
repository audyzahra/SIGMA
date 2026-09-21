<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('report_rewards', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | User penerima reward
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Laporan yang mendapatkan reward
            |--------------------------------------------------------------------------
            */

            $table->foreignId('citizen_report_id')
                ->constrained('citizen_reports')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Jenis reward
            |--------------------------------------------------------------------------
            */

            $table->enum('reward_type',[
                'point',
                'money'
            ]);



            /*
            |--------------------------------------------------------------------------
            | Nilai reward
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount',12,2)
                ->default(0);



            /*
            |--------------------------------------------------------------------------
            | Status reward
            |--------------------------------------------------------------------------
            */

            $table->enum('status',[
                'pending',
                'approved',
                'paid'
            ])
            ->default('pending');



            /*
            |--------------------------------------------------------------------------
            | Admin yang menyetujui
            |--------------------------------------------------------------------------
            */

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Waktu persetujuan
            |--------------------------------------------------------------------------
            */

            $table->timestamp('approved_at')
                ->nullable();



            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('report_rewards');
    }

};
