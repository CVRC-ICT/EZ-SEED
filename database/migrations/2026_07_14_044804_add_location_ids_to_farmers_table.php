<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farmers', function (Blueprint $table) {

            $table->foreignId('province_id')
                ->nullable()
                ->after('email');

            $table->foreignId('municipality_id')
                ->nullable()
                ->after('province_id');

            $table->foreignId('barangay_id')
                ->nullable()
                ->after('municipality_id');

        });
    }


    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {

            $table->dropColumn([
                'province_id',
                'municipality_id',
                'barangay_id'
            ]);

        });
    }
};