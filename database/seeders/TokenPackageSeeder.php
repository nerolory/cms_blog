<?php

namespace Database\Seeders;

use App\Models\TokenPackage;
use Illuminate\Database\Seeder;

/**
 * Сидер БД token package seeder.
 */
class TokenPackageSeeder extends Seeder
{
    /**
     * run.
     */
    public function run(): void
    {
        $packages = [['name' => 'Стартовый', 'token_amount' => 100, 'price_cents' => 9900, 'sort_order' => 10],
            ['name' => 'Стандарт', 'token_amount' => 500, 'price_cents' => 39900, 'sort_order' => 20],
            ['name' => 'Про', 'token_amount' => 1500, 'price_cents' => 99900, 'sort_order' => 30]];
        foreach ($packages as $package) {
            TokenPackage::query()->updateOrCreate(['name' => $package['name']],
                ['token_amount' => $package['token_amount'], 'price_cents' => $package['price_cents'],
                    'currency' => 'RUB', 'is_active' => true, 'sort_order' => $package['sort_order']]);
        }
    }
}
