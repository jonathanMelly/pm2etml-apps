<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('remarks', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->foreignIdFor(\App\Models\User::class, 'author_user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('criteria', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('position');
            $table->timestamps();
        });

        // Seed default criteria
        $criteria = [
            ['name' => 'Rythme de travail', 'description' => 'Capacité à respecter les délais, livrer les fonctionnalités attendues et maintenir une bonne cadence de production.', 'position' => 1],
            ['name' => 'Conscience professionnelle', 'description' => 'Soin apporté au code, à l\'interface et à la validation, avec une recherche constante de fiabilité et de cohérence.', 'position' => 2],
            ['name' => 'Connaissances professionnelles', 'description' => 'Utilisation pertinente des outils et concepts appris, intégration d\'algorithmes personnels et exploitation judicieuse des ressources .NET.', 'position' => 3],
            ['name' => 'Processus de travail', 'description' => 'Structuration claire du projet, séparation des fonctions, gestion des redémarrages et optimisation des étapes de développement.', 'position' => 4],
            ['name' => 'Expression orale et écrite', 'description' => 'Clarté et cohérence des messages à l\'utilisateur, interface soignée et professionnelle, mise en valeur par des choix esthétiques adaptés.', 'position' => 5],
            ['name' => 'Approche écologique et économique', 'description' => 'Recherche d\'efficacité dans l\'utilisation des structures, limitation des redondances et souci de rationaliser le code.', 'position' => 6],
            ['name' => 'Aptitude au travail en équipe', 'description' => 'Lisibilité du code, choix de noms de variables explicites, commentaires pertinents favorisant la compréhension et le partage.', 'position' => 7],
            ['name' => 'Autonomie', 'description' => 'Capacité à explorer de nouvelles solutions, développer des interfaces personnalisées et progresser dans l\'implémentation d\'algorithmes originaux.', 'position' => 8],
        ];
        DB::table('criteria')->insert($criteria);

        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class, 'student_id')->constrained('users');
            $table->foreignIdFor(\App\Models\User::class, 'teacher_id')->constrained('users');
            $table->foreignIdFor(\App\Models\JobDefinition::class)->constrained();
            $table->enum('status', ['encours', 'clos'])->default('encours');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluation_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Evaluation::class)->constrained()->onDelete('cascade');
            $table->integer('version_number');
            $table->foreignIdFor(\App\Models\User::class, 'created_by_user_id')->constrained('users');
            $table->foreignId('general_remark_id')->nullable()->constrained('remarks');
            $table->timestamps();
        });

        Schema::create('appreciation_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\EvaluationVersion::class, 'version_id')->constrained('evaluation_versions')->onDelete('cascade');
            $table->foreignId('criterion_id')->constrained('criteria');
            $table->enum('value', ['NA', 'PA', 'A', 'LA']);
            $table->foreignId('remark_id')->nullable()->constrained('remarks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appreciation_versions');
        Schema::dropIfExists('evaluation_versions');
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('criteria');
        Schema::dropIfExists('remarks');
    }
};
