<?php

namespace Database\Seeders\Rbac;

use Database\Seeders\Rbac\Concerns\ProvisionsRole;
use Illuminate\Database\Seeder;

/**
 * Роль «moderator» — модерация и доступ к панели модератора.
 */
class ModeratorRoleSeeder extends Seeder
{
    use ProvisionsRole;

    /**
     * run.
     */
    public function run(): void
    {
        $this->syncRole('moderator', ['posts.create', 'posts.update.own', 'posts.delete.own', 'posts.moderate',
            'panel.access.moderator', 'ai.orders.manage', 'ViewAny:Post', 'View:Post', 'Update:Post']);
    }
}
