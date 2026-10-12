
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_assignment_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_assignment_id')
                ->constrained('field_assignments')
                ->cascadeOnDelete();

            $table->string('status');
            $table->text('description')->nullable();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['field_assignment_id', 'created_at'],
                'fas_hist_assignment_created_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_assignment_status_histories');
    }
};
