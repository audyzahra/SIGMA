<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('early_warning_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('early_warning_id')->constrained()->restrictOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('action', 16);
            $table->json('snapshot');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['early_warning_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('early_warning_histories');
    }
};
