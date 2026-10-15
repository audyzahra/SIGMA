<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('early_warnings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('incident_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('issued_by')->constrained('users')->restrictOnDelete();
            $table->string('level', 16);
            $table->string('status', 16)->default('active');
            $table->string('source', 32);
            $table->string('area');
            $table->text('reason');
            $table->text('instructions');
            $table->timestamp('issued_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'issued_at']);
            $table->index(['incident_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('early_warnings');
    }
};
