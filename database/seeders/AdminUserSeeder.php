<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('slug', 'super_admin')->firstOrFail();

        User::updateOrCreate(
            ['email' => 'admin@ys-systems.com'],
            [
                'name' => 'YS Admin',
                'password' => 'YS515&Yahya',  // hashed by model cast
                'role_id' => $superAdminRole->id,
                'is_active' => true,
            ]
        );

        $this->command->info('✓ Super admin created: admin@ys-systems.com');
        $this->command->warn('⚠ Change the admin password immediately after first login!');
    }
}
