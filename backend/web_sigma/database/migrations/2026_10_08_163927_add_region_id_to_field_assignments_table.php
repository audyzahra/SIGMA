<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('field_assignments', function (Blueprint $table) {
            $table->foreignId('region_id')
                ->nullable()
                ->after('incident_id')
                ->constrained('regions')
                ->nullOnDelete();

            $table->index('region_id');
        });
    }

    public function down(): void
    {
        Schema::table('field_assignments', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropIndex(['region_id']);
            $table->dropColumn('region_id');
        });
    }
};