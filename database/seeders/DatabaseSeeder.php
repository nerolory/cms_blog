<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * Сидер БД database seeder.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([RolePermissionSeeder::class, MailSettingsSeeder::class, SiteTemplateSeeder::class,
            SearchSettingsSeeder::class, TokenPackageSeeder::class, CategorySeeder::class]);
        // Factories in PostSeeder do not need model events; RBAC/Shield does.
        Model::withoutEvents(function (): void {
            $this->call(PostSeeder::class);
        });
    }
}
