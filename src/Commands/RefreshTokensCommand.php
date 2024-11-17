<?php

namespace JacobTilly\LaraFort\Commands;

use Illuminate\Console\Command;
use JacobTilly\LaraFort\Models\FortnoxCredential;
use JacobTilly\LaraFort\Services\LaraFortService;

class RefreshTokensCommand extends Command
{
    protected $signature = 'larafort:refresh-tokens';
    protected $description = 'Refresh Fortnox access and refresh tokens';

    public function handle(LaraFortService $service)
    {
        $this->info('Starting token refresh...');

        $environments = FortnoxCredential::distinct()
            ->pluck('environment');

        foreach ($environments as $environment) {
            $this->info("Refreshing tokens for {$environment} environment...");

            try {
                $credentials = FortnoxCredential::where('environment', $environment)
                    ->latest()
                    ->first();

                if ($credentials) {
                    $service->refreshTokens($credentials);
                    $this->info("Tokens refreshed successfully for {$environment}");
                }
            } catch (\Exception $e) {
                $this->error("Failed to refresh tokens for {$environment}: " . $e->getMessage());
            }
        }

        $this->info('Token refresh completed!');
    }
}
