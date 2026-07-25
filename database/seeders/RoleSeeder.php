<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name'        => 'Super Admin',
                'slug'        => 'super_admin',
                'description' => 'Full access to everything.',
                'permissions' => ['*'],
            ],
            [
                'name'        => 'Admin',
                'slug'        => 'admin',
                'description' => 'Full access except user management.',
                'permissions' => [
                    'manage_products',
                    'manage_documentation',
                    'manage_roadmap',
                    'manage_updates',
                    'manage_careers',
                    'manage_media',
                    'manage_settings',
                    'manage_contact_requests',
                    'manage_static_pages',
                    'manage_faqs',
                    'manage_menus',
                    'manage_homepage',
                    'view_audit_logs',
                    'view_products',
                ],
            ],
            [
                'name'        => 'Editor',
                'slug'        => 'editor',
                'description' => 'Manage content only.',
                'permissions' => [
                    'manage_documentation',
                    'manage_updates',
                    'manage_roadmap',
                    'manage_media',
                    'manage_faqs',
                    'manage_homepage',
                    'view_products',
                ],
            ],
            [
                'name'        => 'Content Manager',
                'slug'        => 'content_manager',
                'description' => 'Manage public-facing content.',
                'permissions' => [
                    'manage_documentation',
                    'manage_updates',
                    'manage_careers',
                    'manage_static_pages',
                    'manage_faqs',
                    'manage_homepage',
                    'view_products',
                ],
            ],
            [
                'name'        => 'Support',
                'slug'        => 'support',
                'description' => 'Handle contact requests.',
                'permissions' => [
                    'manage_contact_requests',
                    'view_products',
                ],
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }

        $this->command->info('✓ Roles seeded.');
    }
}
