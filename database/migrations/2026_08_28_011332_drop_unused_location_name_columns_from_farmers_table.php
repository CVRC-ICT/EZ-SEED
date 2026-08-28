<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * These raw string columns (province, municipality, barangay) are
     * leftover/unused — the real location data lives in province_id,
     * municipality_id, barangay_id, which map to the Province,
     * Municipality, and Barangay relations. Because these columns share
     * the exact same names as the belongsTo() relation methods, Eloquent's
     * attribute lookup finds the (always-null) raw column first and never
     * falls through to the relationship — silently breaking
     * $farmer->province, $farmer->municipality, $farmer->barangay
     * anywhere a Farmer is loaded with all columns (e.g. farmers/index,
     * farmers/show). Dropping them removes the shadowing at the source.
     */
    public function up(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->dropColumn(['province', 'municipality', 'barangay']);
        });
    }

    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->string('barangay')->nullable();
        });
    }
};