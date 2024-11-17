<?php

namespace JacobTilly\LaraFort\Commands;

use Illuminate\Console\Command;
use JacobTilly\LaraFort\Models\FortnoxCredential;
use JacobTilly\LaraFort\Services\LaraFortService;

class MigrateCommand extends Command
{
    protected $signature = 'larafort:migrate';
    protected $description = 'Migrate Fortnox credentials to a new environment';

    public function handle(LaraFortService $service)
    {
        $this->info('Migrating Fortnox credentials...');

        // Get credentials
        $clientId = $this->ask('Enter Fortnox Client ID:');
        $clientSecret = $this->secret('Enter Fortnox Client Secret:');
        $refreshToken = $this->secret('Enter Refresh Token from source environment:');

        // Select environment
        $environment = $this->choice(
            'Which environment is this?',
            ['live', 'test'],
            'live'
        );

        // Update .env
        $this->updateEnvironmentFile([
            'FORTNOX_CLIENT_ID' => $clientId,
            'FORTNOX_CLIENT_SECRET' => $clientSecret,
            'FORTNOX_ENVIRONMENT' => $environment,
        ]);

        // Use refresh token to get new access token
        $response = $service->refreshTokenWithCredentials($clientId, $clientSecret, $refreshToken);

        FortnoxCredential::create([
            'environment' => $environment,
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'],
            'expires_at' => now()->addHour(),
        ]);

        $this->info('Credentials migrated successfully!');
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
                $envFile .= "\n{$key}={$value}";
            }
        }

        file_put_contents(base_path('.env'), $envFile);
    }
}
