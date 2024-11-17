<?php

namespace JacobTilly\LaraFort\Commands;

use Illuminate\Console\Command;
use JacobTilly\LaraFort\Models\FortnoxCredential;

class SwitchEnvironmentCommand extends Command
{
    protected $signature = 'larafort:env';
    protected $description = 'Switch between test and live Fortnox environments';

    public function handle()
    {
        $currentEnv = config('larafort.environment', 'live');

        // Check if both environments are configured
        $environments = FortnoxCredential::distinct()
            ->pluck('environment')
            ->toArray();

        if (empty($environments)) {
            $this->error('No Fortnox environments configured. Run php artisan larafort:install first.');
            return 1;
        }

        if (count($environments) === 1) {
            $this->error('Only ' . $environments[0] . ' environment is configured.');
            $this->info('To configure another environment, run: php artisan larafort:install');
            return 1;
        }

        // Show current status
        $this->info('Current environment: ' . $currentEnv);

        // Switch to the other environment
        $newEnv = $currentEnv === 'live' ? 'test' : 'live';

        if ($this->confirm("Switch to {$newEnv} environment?", true)) {
            $this->updateEnvironmentFile([
                'FORTNOX_ENVIRONMENT' => $newEnv
            ]);

            $this->info('✓ Switched to ' . $newEnv . ' environment');
            return 0;
        }

        $this->info('Environment switch cancelled.');
        return 0;
    }

    protected function updateEnvironmentFile(array $values)
    {
        $envFile = file_get_contents(base_path('.env'));

        foreach ($values as $key => $value) {
            if (str_contains($envFile, $key . '=')) {
                $envFile = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envFile
                );
            } else {
                $envFile .= PHP_EOL . "{$key}={$value}";
            }
        }

        file_put_contents(base_path('.env'), $envFile);
    }
}
