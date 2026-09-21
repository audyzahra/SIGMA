<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('report_status_histories', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Relasi laporan masyarakat
            |--------------------------------------------------------------------------
            */

            $table->foreignId('citizen_report_id')
                ->constrained('citizen_reports')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Status laporan
            |--------------------------------------------------------------------------
            */

            $table->enum('status',[
                'submitted',
                'verified',
                'process',
                'completed'
            ])
            ->default('submitted');



            /*
            |--------------------------------------------------------------------------
            | Keterangan perubahan
            |--------------------------------------------------------------------------
            */

            $table->text('description')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | User yang mengubah status
            |--------------------------------------------------------------------------
            */

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();



            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('report_status_histories');
    }

};
