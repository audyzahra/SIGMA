
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE field_assignments
            MODIFY priority_level
            ENUM('low', 'medium', 'high', 'critical')
            NOT NULL
        ");
    }

    public function down(): void
    {
        // Jangan menghapus nilai enum 'extreme' atau 'critical'
        // secara otomatis karena data lama mungkin masih menggunakannya.
    }
};
