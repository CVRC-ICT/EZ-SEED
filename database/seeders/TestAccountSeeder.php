<?php

namespace Database\Seeders;

use App\Models\DAPersonnel;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestAccountSeeder extends Seeder
{
    /**
     * DEVELOPMENT ONLY — do not run this seeder against production.
     *
     * Creates two throwaway login accounts so you can preview both
     * role-based dashboard views while building. Safe to re-run —
     * uses updateOrCreate so it won't duplicate.
     *
     * Administrator login: admin@ezseed.test      / password
     * User login:          enumerator@ezseed.test / password
     *
     * In production, create the initial Administrator via a dedicated,
     * secure setup step instead (see PATCH_INSTRUCTIONS.md / README note
     * on initial Administrator creation) — never ship this seeder's
     * hardcoded password to a live environment.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('TestAccountSeeder skipped: refusing to run in production.');
            return;
        }

        $administratorRole = Role::firstOrCreate(['name' => 'Administrator']);
        $userRole          = Role::firstOrCreate(['name' => 'User']);

        $admin = User::updateOrCreate(
            ['email' => 'admin@ezseed.test'],
            [
                'name'                  => 'Test Administrator',
                'password'              => Hash::make('password'),
                'role_id'               => $administratorRole->id,
                'status'                => 'active',
                'must_change_password'  => false,
            ]
        );

        $regularUser = User::updateOrCreate(
            ['email' => 'enumerator@ezseed.test'],
            [
                'name'                  => 'Test User',
                'password'              => Hash::make('password'),
                'role_id'               => $userRole->id,
                'status'                => 'active',
                'must_change_password'  => false,
            ]
        );

        DAPersonnel::updateOrCreate(
            ['user_id' => $regularUser->id],
            [
                'name'            => 'Test User',
                'position'        => 'Agricultural Enumerator',
                'office'          => 'DA Regional Field Office No. 02',
                'contact_number'  => '09000000000',
                'province'        => 'Cagayan',
            ]
        );

        $this->command->info('Test accounts ready:');
        $this->command->info('  Administrator: admin@ezseed.test / password');
        $this->command->info('  User:          enumerator@ezseed.test / password');
    }
}
