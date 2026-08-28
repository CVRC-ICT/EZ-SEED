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
        Schema::table('d_a_personnels', function (Blueprint $table) {

            $table->string('name');
            $table->string('position');
            $table->string('office');
            $table->string('contact_number')->nullable();
            $table->string('province')->nullable();

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('d_a_personnels', function (Blueprint $table) {

            $table->dropColumn([
                'name',
                'position',
                'office',
                'contact_number',
                'province'
            ]);

        });
    }
};