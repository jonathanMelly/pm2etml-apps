<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Migrate EvaluationVersion remarks
        $versions = DB::table('evaluation_versions')->whereNotNull('general_remark_id')->get();
        foreach ($versions as $version) {
            $remark = DB::table('remarks')->find($version->general_remark_id);
            if ($remark) {
                DB::table('comments')->insert([
                    'commentable_type' => 'App\Models\EvaluationVersion',
                    'commentable_id' => $version->id,
                    'user_id' => $remark->author_user_id,
                    'body' => $remark->text,
                    'created_at' => $remark->created_at,
                    'updated_at' => $remark->updated_at,
                ]);
            }
        }

        // Migrate AppreciationVersion remarks
        $appreciations = DB::table('appreciation_versions')->whereNotNull('remark_id')->get();
        foreach ($appreciations as $appreciation) {
            $remark = DB::table('remarks')->find($appreciation->remark_id);
            if ($remark) {
                DB::table('comments')->insert([
                    'commentable_type' => 'App\Models\AppreciationVersion',
                    'commentable_id' => $appreciation->id,
                    'user_id' => $remark->author_user_id,
                    'body' => $remark->text,
                    'created_at' => $remark->created_at,
                    'updated_at' => $remark->updated_at,
                ]);
            }
        }

        // Drop columns and table
        Schema::table('evaluation_versions', function (Blueprint $table) {
            $table->dropForeign(['general_remark_id']);
            $table->dropColumn('general_remark_id');
        });

        Schema::table('appreciation_versions', function (Blueprint $table) {
            $table->dropForeign(['remark_id']);
            $table->dropColumn('remark_id');
        });

        Schema::dropIfExists('remarks');
    }

    public function down(): void
    {
        // Recreate remarks table
        Schema::create('remarks', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->foreignId('author_user_id')->constrained('users');
            $table->timestamps();
        });

        // Add columns back
        Schema::table('evaluation_versions', function (Blueprint $table) {
            $table->foreignId('general_remark_id')->nullable()->constrained('remarks');
        });

        Schema::table('appreciation_versions', function (Blueprint $table) {
            $table->foreignId('remark_id')->nullable()->constrained('remarks');
        });

        // Restore data (reverse migration is complex, simplified here)
        // In a real scenario, we might want to restore data from comments back to remarks
        // but for this task, we assume forward migration is the main goal.
    }
};
