<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {
        DB::statement("
            ALTER TABLE report_status_histories
            MODIFY status ENUM(
                'submitted',
                'pending',
                'verified',
                'rejected',
                'process',
                'completed'
            )
            DEFAULT 'submitted'
        ");
    }


    public function down(): void
    {
        DB::statement("
            ALTER TABLE report_status_histories
            MODIFY status ENUM(
                'submitted',
                'verified',
                'process',
                'completed'
            )
            DEFAULT 'submitted'
        ");
    }

};
