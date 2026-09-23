<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE ai_response_recommendations
            MODIFY recommendation_type ENUM(
                'deploy_team',
                'aerial_patrol',
                'evacuation',
                'community_alert',
                'water_source_check'
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE ai_response_recommendations
            MODIFY recommendation_type ENUM(
                'deploy_team',
                'aerial_patrol',
                'evacuation',
                'community_alert'
            )
        ");
    }
};