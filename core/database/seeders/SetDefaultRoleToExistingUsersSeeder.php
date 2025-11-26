<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SetDefaultRoleToExistingUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set role to 'customer' for all existing users without a role
        // This ensures backward compatibility
        User::whereNull('role')
            ->orWhere('role', '')
            ->update(['role' => 'customer']);

        $this->command->info('Default role (customer) has been set for existing users.');
    }
}
