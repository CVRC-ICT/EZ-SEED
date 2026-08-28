<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Wires together the three tables that already exist (users, roles,
     * d_a_personnels) but currently have no relationship to each other.
     * Every column add is guarded with hasColumn() so this migration is
     * safe to run even if you've already added some of these manually.
     */
    public function up(): void
    {
        // 1. roles table needs a name (Admin / Supervisor / Enumerator)
        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'name')) {
                $table->string('name')->unique()->after('id');
            }
        });

        // 2. users need a role_id so we know Admin vs Supervisor vs Enumerator
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('roles')
                      ->nullOnDelete();
            }
        });

        // 3. d_a_personnels needs a user_id so an Enumerator's login maps
        //    to their personnel record (and therefore to their surveys)
        Schema::table('d_a_personnels', function (Blueprint $table) {
            if (! Schema::hasColumn('d_a_personnels', 'user_id')) {
                $table->foreignId('user_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('users')
                      ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('d_a_personnels', function (Blueprint $table) {
            if (Schema::hasColumn('d_a_personnels', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropConstrainedForeignId('role_id');
            }
        });

        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
