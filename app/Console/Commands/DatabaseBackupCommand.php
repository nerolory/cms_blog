<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Artisan-команда database backup command.
 */
class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup
                            {--path= : Output directory (default: storage/backups)}
                            {--connection= : Database connection name}';

    protected $description = 'Create a PostgreSQL dump backup using pg_dump';

    /**
     * Выполняет команду.

     *
     * @return int
     */
    public function handle(): int
    {
        $connectionOption = $this->option('connection');
        $defaultConnection = config('database.default');
        $connection = is_string($connectionOption) && $connectionOption !== ''
            ? $connectionOption
            : (is_string($defaultConnection) ? $defaultConnection : 'pgsql');
        $rawConfig = config("database.connections.{$connection}");
        if (! is_array($rawConfig) || ($rawConfig['driver'] ?? null) !== 'pgsql') {
            $this->error('db:backup currently supports PostgreSQL connections only.');

            return self::FAILURE;
        }
        /** @var array{
         *     host?: string|null,
         *     port?: string|int|null,
         *     database?: string|null,
         *     username?: string|null,
         *     password?: string|null
         * } $config */
        $config = $rawConfig;
        $outputDir = (string) ($this->option('path') ?: storage_path('backups'));
        File::ensureDirectoryExists($outputDir);
        $filename = sprintf('backup-%s-%s.sql', $connection, now()->format('Ymd-His'));
        $outputPath = $outputDir.DIRECTORY_SEPARATOR.$filename;
        $host = (string) ($config['host'] ?? '127.0.0.1');
        $port = (string) ($config['port'] ?? '5432');
        $database = (string) ($config['database'] ?? '');
        $username = (string) ($config['username'] ?? '');
        $password = (string) ($config['password'] ?? '');
        if ($database === '' || $username === '') {
            throw new RuntimeException('Database name and username are required for backup.');
        }
        $this->info('Creating backup: '.$outputPath);
        $result = Process::timeout(600)->env(['PGPASSWORD' => $password])->run(['pg_dump', '-h', $host, '-p', $port,
            '-U', $username, '-d', $database, '--no-owner', '--no-acl', '-f', $outputPath]);
        if (! $result->successful()) {
            $this->error(trim($result->errorOutput()) ?: 'pg_dump failed.');

            return self::FAILURE;
        }
        $this->info('Backup completed successfully.');

        return self::SUCCESS;
    }
}
