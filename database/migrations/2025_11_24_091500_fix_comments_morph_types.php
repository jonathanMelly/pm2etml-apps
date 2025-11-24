<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Constants\MorphTargets;

return new class extends Migration {
    public function up(): void
    {
        DB::table('comments')
            ->where('commentable_type', 'App\Models\EvaluationVersion')
            ->update(['commentable_type' => MorphTargets::MORPH2_EVALUATION_VERSION]);

        DB::table('comments')
            ->where('commentable_type', 'App\Models\AppreciationVersion')
            ->update(['commentable_type' => MorphTargets::MORPH2_APPRECIATION_VERSION]);
    }

    public function down(): void
    {
        DB::table('comments')
            ->where('commentable_type', MorphTargets::MORPH2_EVALUATION_VERSION)
            ->update(['commentable_type' => 'App\Models\EvaluationVersion']);

        DB::table('comments')
            ->where('commentable_type', MorphTargets::MORPH2_APPRECIATION_VERSION)
            ->update(['commentable_type' => 'App\Models\AppreciationVersion']);
    }
};
