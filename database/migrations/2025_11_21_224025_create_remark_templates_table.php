<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('remark_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text');
            $table->foreignIdFor(\App\Models\User::class)->nullable()->constrained()->onDelete('cascade');
            $table->foreignIdFor(\App\Models\Criterion::class)->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Seed some default templates
        $templates = [
            ['title' => 'Excellent', 'text' => 'Excellent travail, continue ainsi !'],
            ['title' => 'Bon travail', 'text' => 'Bon travail dans l\'ensemble.'],
            ['title' => 'A améliorer', 'text' => 'Certains points sont à améliorer.'],
            ['title' => 'Non rendu', 'text' => 'Le travail n\'a pas été rendu.'],
        ];
        
        DB::table('remark_templates')->insert($templates);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remark_templates');
    }
};
