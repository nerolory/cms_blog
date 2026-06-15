<?php

namespace Database\Seeders\Rbac;

use Database\Seeders\Rbac\Concerns\ProvisionsRole;
use Illuminate\Database\Seeder;

/**
 * Роль «user» — базовые права автора.
 */
class UserRoleSeeder extends Seeder
{
    use ProvisionsRole;

    /**
     * run.
     */
    public function run(): void
    {
        $this->syncRole('user', ['posts.create', 'posts.update.own', 'posts.delete.own', 'ai.orders.request',
            'tokens.purchase']);
    }
}
