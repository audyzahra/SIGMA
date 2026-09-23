<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('field_assignments', function (Blueprint $table): void {
            $table->foreignId('accepted_by')->nullable()->after('assigned_by')->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable()->after('assigned_at');
        });
    }

    public function down(): void
    {
        Schema::table('field_assignments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('accepted_by');
            $table->dropColumn('accepted_at');
        });
    }
};
