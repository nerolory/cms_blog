<?php

use App\Models\User;
use App\Support\Config\ProductionConfigGuard;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

ProductionConfigGuard::assertOwnerPasswordSafe();

$email = (string) config('seeding.owner.email');
$password = (string) config('seeding.owner.password');

$owner = User::query()
    ->where('email', $email)
    ->orWhereHas('roles', fn ($query) => $query->where('name', 'owner'))
    ->first();

if ($owner === null) {
    fwrite(STDERR, "Owner user not found. Run: php artisan db:seed --force\n");
    exit(1);
}

$owner->forceFill([
    'email' => $email,
    'password' => $password,
    'email_verified_at' => now(),
])->save();

if (! $owner->hasRole('owner')) {
    $owner->assignRole('owner');
}

echo "Owner restored: {$email} / {$password}\n";
