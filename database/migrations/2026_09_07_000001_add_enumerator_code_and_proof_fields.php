<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('d_a_personnels', function (Blueprint $table) {
            // Unique code enumerators type once to auto-fill their info.
            $table->string('code')->nullable()->unique()->after('id');
        });

        Schema::table('surveys', function (Blueprint $table) {
            // Base64-encoded proof-of-interview photo and signature.
            // Stored as long text since they are Data URLs, not file paths.
            $table->longText('proof_photo')->nullable()->after('payload');
            $table->longText('proof_signature')->nullable()->after('proof_photo');
        });
    }

    public function down(): void
    {
        Schema::table('d_a_personnels', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn(['proof_photo', 'proof_signature']);
        });
    }
};
