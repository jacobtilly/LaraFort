<?php

namespace JacobTilly\LaraFort\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class UninstallCommand extends Command
{
    protected $signature = 'larafort:uninstall {--force : Force the operation without confirmation}';
    protected $description = 'Remove all LaraFort files, tables and configuration';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('This will remove all LaraFort data and configuration. Are you sure?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->info('Uninstalling LaraFort...');

        // Remove database table
        if (Schema::hasTable('fortnox_credentials')) {
            Schema::dropIfExists('fortnox_credentials');
            $this->info('✓ Removed database table');
        }

        // Remove config file
        if (File::exists(config_path('larafort.php'))) {
            File::delete(config_path('larafort.php'));
            $this->info('✓ Removed config file');
        }

        // Remove environment variables
        $this->removeEnvironmentVariables([
            'FORTNOX_CLIENT_ID',
            'FORTNOX_CLIENT_SECRET',
            'FORTNOX_ENVIRONMENT',
            'FORTNOX_CALLBACK_URL',
        ]);
        $this->info('✓ Removed environment variables');

        // Remove migration files
        $migrationFile = $this->findMigrationFile('create_fortnox_credentials_table');
        if ($migrationFile) {
            File::delete(database_path('migrations/' . $migrationFile));
            $this->info('✓ Removed migration file');
        }

        $this->info('LaraFort has been uninstalled successfully!');
        $this->info('To complete the removal, remove the package from composer.json and run composer update');
    }

    protected function removeEnvironmentVariables(array $keys)
    {
        $envFile = base_path('.env');

        if (File::exists($envFile)) {
            $content = File::get($envFile);

            foreach ($keys as $key) {
                $content = preg_replace('/^' . $key . '=.*\n/m', '', $content);
            }

            File::put($envFile, $content);
        }
    }

    protected function findMigrationFile(string $name): ?string
    {
        $files = File::glob(database_path('migrations/*'));

        foreach ($files as $file) {
            if (str_contains($file, $name)) {
                return basename($file);
            }
        }

        return null;
    }
}
