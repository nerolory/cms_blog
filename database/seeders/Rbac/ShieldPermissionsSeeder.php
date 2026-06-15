<?php

namespace Database\Seeders\Rbac;

use Filament\Facades\Filament;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionRegistrar;

/**
 * Registers Filament Shield permissions (ViewAny:Post, etc.) in the database.
 */
class ShieldPermissionsSeeder extends Seeder
{
    /**
     * run.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Artisan::call('shield:generate', ['--all' => true, '--panel' => 'admin', '--option' => 'permissions',
            '--ignore-existing-policies' => true, '--no-interaction' => true]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
