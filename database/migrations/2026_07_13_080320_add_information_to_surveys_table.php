<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {

            $table->foreignId('farmer_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('d_a_personnel_id')
                  ->nullable()
                  ->constrained('d_a_personnels')
                  ->nullOnDelete();

            // Consent Form
            $table->boolean('assisted_by_da_personnel')
                  ->default(false);

            $table->enum('status', [
                'draft',
                'submitted',
                'approved'
            ])->default('draft');

            $table->timestamp('submitted_at')
                  ->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {

            $table->dropForeign(['farmer_id']);
            $table->dropForeign(['d_a_personnel_id']);

            $table->dropColumn([
                'farmer_id',
                'd_a_personnel_id',
                'assisted_by_da_personnel',
                'status',
                'submitted_at'
            ]);

        });
    }
};