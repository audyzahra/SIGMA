
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('field_assignments', function (Blueprint $table) {
            $table->dropForeign(['incident_id']);

            $table->unsignedBigInteger('incident_id')
                ->nullable()
                ->change();

            $table->foreign('incident_id')
                ->references('id')
                ->on('incidents')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Jangan mengembalikan incident_id menjadi NOT NULL
        // sebelum semua baris dengan incident_id NULL ditangani.
    }
};
