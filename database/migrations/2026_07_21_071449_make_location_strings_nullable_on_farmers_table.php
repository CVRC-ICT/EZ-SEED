<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The `province`, `municipality`, and `barangay` varchar columns were
     * left over from before the `province_id` / `municipality_id` /
     * `barangay_id` foreign keys were introduced. They are currently
     * NOT NULL with no default, which causes Farmer::create() to fail
     * with a SQL error since the Farmer Portal registration form only
     * submits the `_id` versions.
     */
    public function up(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->string('province')->nullable()->change();
            $table->string('municipality')->nullable()->change();
            $table->string('barangay')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->string('province')->nullable(false)->change();
            $table->string('municipality')->nullable(false)->change();
            $table->string('barangay')->nullable(false)->change();
        });
    }
};