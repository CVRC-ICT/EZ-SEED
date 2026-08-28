<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Administrator -> full account management + system-wide dashboard access
     * User          -> standard DA Dashboard access (farmers, surveys,
     *                  analytics, recommendations, reports)
     */
    public function run(): void
    {
        foreach (['Administrator', 'User'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }
}
