<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\RoleRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Support\Config\ProductionConfigGuard;
use App\Support\TypeCast;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Artisan-команда bootstrap application command.
 *
 * @property-read UserRepositoryContract $users
 * @property-read RoleRepositoryContract $roles
 */
class BootstrapApplicationCommand extends Command
{
    protected $signature = 'app:bootstrap {--force : Seed even when owner already exists}';

    protected $description = 'Migrate and seed demo data when the database is empty (local bootstrap).';

    public function __construct(protected UserRepositoryContract $users, protected RoleRepositoryContract $roles)
    {
        parent::__construct();
    }

    /**
     * Выполняет команду.

     *
     * @return int
     */
    public function handle(): int
    {
        if (! Schema::hasTable('migrations')) {
            $this->components->info('Running migrations…');
            $this->call('migrate', ['--force' => true]);
        }
        $ownerEmail = TypeCast::string(config('seeding.owner.email'));
        $needsSeed = $this->users->count() === 0 || $this->roles->count() === 0 || ! $this->users
            ->existsByEmail($ownerEmail);
        if (! $needsSeed && ! $this->option('force')) {
            $this->components->info('Database already bootstrapped.');

            return self::SUCCESS;
        }
        ProductionConfigGuard::assertOwnerPasswordSafe();
        $this->components->info('Seeding demo data…');
        $this->call('db:seed', ['--force' => true]);
        $owner = $this->users->findByEmail($ownerEmail);
        $this->components->info(sprintf('Bootstrap complete: %d users, owner %s', $this->users->count(),
            $owner !== null ? $ownerEmail : 'missing'));

        return self::SUCCESS;
    }
}
