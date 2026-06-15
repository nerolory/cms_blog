<?php

namespace Database\Seeders\Rbac;

use App\Models\User;
use App\Support\Config\ProductionConfigGuard;
use Illuminate\Database\Seeder;

/**
 * Системный пользователь owner (dev/bootstrap).
 */
class OwnerUserSeeder extends Seeder
{
    /**
     * run.
     */
    public function run(): void
    {
        ProductionConfigGuard::assertOwnerPasswordSafe();
        $owner = User::query()->firstOrCreate(['email' => config('seeding.owner.email')],
            ['name' => config('seeding.owner.name'), 'password' => config('seeding.owner.password'),
                'theme' => 'default', 'email_verified_at' => now()]);
        if ($owner->email_verified_at === null) {
            $owner->forceFill(['email_verified_at' => now()])->save();
        }
        if (! $owner->hasRole('owner')) {
            $owner->assignRole('owner');
        }
    }
}
