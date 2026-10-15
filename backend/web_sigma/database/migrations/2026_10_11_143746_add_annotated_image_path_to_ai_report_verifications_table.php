
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_report_verifications', function (Blueprint $table) {
            $table->string('annotated_image_path')
                ->nullable()
                ->after('detected_objects');
        });
    }

    public function down(): void
    {
        Schema::table('ai_report_verifications', function (Blueprint $table) {
            $table->dropColumn('annotated_image_path');
        });
    }
};
