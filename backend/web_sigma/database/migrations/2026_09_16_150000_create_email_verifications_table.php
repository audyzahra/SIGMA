<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('otp_code', 6);
            $table->timestamp('expired_at');
            $table->timestamps();
            $table->index(['email', 'otp_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verifications');
    }
};
