<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('region_id')->nullable()->after('organization_id')->constrained()->nullOnDelete();
        });

        Schema::table('early_warning_histories', function (Blueprint $table): void {
            $table->uuid('idempotency_key')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('early_warning_histories', function (Blueprint $table): void {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn('idempotency_key');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('region_id');
        });
    }
};
