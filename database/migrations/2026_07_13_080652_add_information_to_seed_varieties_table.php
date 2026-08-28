<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seed_varieties', function (Blueprint $table) {

            $table->string('variety_name');

            $table->enum('seed_type', [
                'Hybrid',
                'Inbred'
            ]);

            $table->enum('season', [
                'Dry Season',
                'Wet Season'
            ])->nullable();

            $table->string('crop_type')
                  ->default('Rice');

            $table->boolean('is_active')
                  ->default(true);

        });
    }

    public function down(): void
    {
        Schema::table('seed_varieties', function (Blueprint $table) {

            $table->dropColumn([
                'variety_name',
                'seed_type',
                'season',
                'crop_type',
                'is_active'
            ]);

        });
    }
};