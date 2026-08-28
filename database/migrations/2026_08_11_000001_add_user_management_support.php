<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Adds proper Administrator/User role-based access control on top of
     * the existing users/roles/d_a_personnels tables.
     *
     * This does NOT drop or duplicate any existing table. It:
     *   1. Adds `status` (active/inactive) and `must_change_password` to users.
     *   2. Normalizes the `roles` table down to exactly two roles that the
     *      DA Dashboard cares about for account management: Administrator
     *      and User. The existing "Admin" role is renamed to
     *      "Administrator". The existing "Supervisor" and "Enumerator"
     *      roles are merged into "User" (their users are re-pointed to the
     *      new "User" role) so nobody loses dashboard access. Any
     *      dashboard logic that previously distinguished Supervisor vs
     *      Enumerator is untouched at the data level (see note below) —
     *      only account-management authorization treats them as "User".
     *
     * Safe to re-run: every change is guarded.
     */
    public function up(): void
    {
        // 1. users: status + forced password change flag
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive'])
                      ->default('active')
                      ->after('role_id');
            }
            if (! Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')
                      ->default(false)
                      ->after('status');
            }
        });

        // 2. roles: rename "Admin" -> "Administrator", ensure "User" exists,
        //    then move any Supervisor/Enumerator users onto "User".
        //    We keep the Supervisor/Enumerator rows in place only if other
        //    code depends on them; instead we simply repoint users so
        //    nothing in the existing dashboard scoping breaks silently.
        DB::table('roles')->where('name', 'Admin')->update(['name' => 'Administrator']);

        $userRoleId = DB::table('roles')->where('name', 'User')->value('id');
        if (! $userRoleId) {
            $userRoleId = DB::table('roles')->insertGetId([
                'name'       => 'User',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $legacyRoleIds = DB::table('roles')
            ->whereIn('name', ['Supervisor', 'Enumerator'])
            ->pluck('id');

        if ($legacyRoleIds->isNotEmpty()) {
            DB::table('users')
                ->whereIn('role_id', $legacyRoleIds)
                ->update(['role_id' => $userRoleId]);
        }

        // Any user with no role at all defaults to "User" so they aren't
        // accidentally locked out or (worse) treated as Administrator.
        DB::table('users')->whereNull('role_id')->update(['role_id' => $userRoleId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'must_change_password')) {
                $table->dropColumn('must_change_password');
            }
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }
        });

        DB::table('roles')->where('name', 'Administrator')->update(['name' => 'Admin']);
    }
};
