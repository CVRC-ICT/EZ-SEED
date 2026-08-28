<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seed_preferences', function (Blueprint $table) {

            $table->foreignId('survey_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('seed_variety_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->enum('season', [
                'Dry Season',
                'Wet Season'
            ]);

            $table->enum('seed_type', [
                'Hybrid',
                'Inbred'
            ]);

            $table->integer('preference_rank')
                  ->nullable();

            $table->text('reason')
                  ->nullable();

            $table->text('problems_encountered')
                  ->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('seed_preferences', function (Blueprint $table) {

            $table->dropForeign(['survey_id']);
            $table->dropForeign(['seed_variety_id']);

            $table->dropColumn([
                'survey_id',
                'seed_variety_id',
                'season',
                'seed_type',
                'preference_rank',
                'reason',
                'problems_encountered'
            ]);

        });
    }
};