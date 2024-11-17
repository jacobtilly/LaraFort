<?php

namespace JacobTilly\LaraFort\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use JacobTilly\LaraFort\Services\LaraFortService;
use JacobTilly\LaraFort\Models\FortnoxCredential;
use Illuminate\Support\Facades\Http;
use JacobTilly\LaraFort\Enums\FortnoxScope;

class InstallCommand extends Command
{
    protected $signature = 'larafort:install';
    protected $description = 'Install and configure LaraFort';

    protected $service;
    protected $state;
    protected $callbackUrl;

    public function __construct(LaraFortService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $this->info('Installing LaraFort...');

        // Set cache flag
        Cache::put('larafort_installing', true, now()->addMinutes(10));

        // Publish config and migrations
        $this->call('vendor:publish', [
            '--tag' => 'larafort',
            '--force' => true,
        ]);

        // Run migrations
        $this->call('migrate');

        $credentials = $this->getCredentials();
        $clientId = $credentials['client_id'];
        $clientSecret = $credentials['client_secret'];

        // Select scopes
        $scopes = $this->selectScopes();

        // Select environment
        $environment = $this->choice(
            'Which Fortnox environment to connect?',
            ['live', 'test'],
            'live'
        );

        // Handle callback URL
        $this->callbackUrl = $this->handleCallbackUrl();

        // Update .env
        $this->updateEnvironmentFile([
            'FORTNOX_CLIENT_ID' => $clientId,
            'FORTNOX_CLIENT_SECRET' => $clientSecret,
            'FORTNOX_ENVIRONMENT' => $environment,
        ]);

        try {
            // Start OAuth flow
            $this->performOAuthFlow($clientId, $clientSecret, $environment, $scopes);

            $this->info('LaraFort installed successfully for ' . $environment . ' environment!');

            // Offer to connect other environment
            if ($this->confirm('Would you like to connect the other environment as well?')) {
                $otherEnv = $environment === 'live' ? 'test' : 'live';
                config(['larafort.environment' => $otherEnv]);

                $this->performOAuthFlow($clientId, $clientSecret, $otherEnv, $scopes);
                $this->info('LaraFort installed successfully for ' . $otherEnv . ' environment!');
            }

        } catch (\Exception $e) {
            $this->error('Installation failed: ' . $e->getMessage());
            return 1;
        }

        $this->info('Installation completed, and schedule to refresh tokens is set up.');

        // Clear cache flag
        Cache::forget('larafort_installing');

        return 0;
    }

    protected function handleCallbackUrl(): string
    {
        $urlType = $this->choice(
            'How would you like to handle the callback URL?',
            [
                'manual' => 'I already have a tunnel/URL ready (recommended)',
                'expose' => 'Start an Expose tunnel automatically (**very** experimental, only works 1% of the time... Requires Herd, configured with Expose.)',
                'production' => 'This is a production server with public URL',
            ],
            'manual'
        );

        $url = '';
        switch ($urlType) {
            case 'manual':
                $url = $this->ask('Please enter your callback URL (e.g., https://your-tunnel.expose.dev)');
                break;

            case 'production':
                $url = $this->ask('Please enter your production URL (e.g., https://your-site.com)');
                break;

            case 'expose':
                $this->info('Starting Expose tunnel...');
                try {
                    $url = $this->service->startTunnel();
                    $this->info("Tunnel started at: $url");
                } catch (\Exception $e) {
                    $this->error('Failed to start Expose tunnel automatically.');
                    $url = $this->ask('Please start a tunnel manually and enter the URL:');
                }
                break;
        }

        $callbackUrl = rtrim($url, '/') . '/fortnox/callback';

        $this->info('Important: Add this callback URL to your Fortnox Developer Portal:');
        $this->newLine();
        $this->info($callbackUrl);
        $this->newLine();
        $this->info('Steps:');
        $this->info('1. Go to https://developer.fortnox.se/');
        $this->info('2. Log in and go to your application');
        $this->info('3. Add the callback URL above to "Redirect URLs"');
        $this->info('4. Save the changes');
        $this->newLine();

        $this->ask('Press Enter when you have added the callback URL in Fortnox Developer Portal');

        return $url;
    }

    protected function selectScopes(): array
    {
        $selectionType = $this->choice(
            'How would you like to select API scopes?',
            [
                'all' => 'Include all available scopes',
                'manual' => 'Manually select individual scopes',
            ],
            'all'
        );

        if ($selectionType === 'all') {
            $scopes = array_keys(FortnoxScope::getDefaultScopes());
            $this->info('Including all available scopes:');
            $this->info('- ' . implode("\n- ", $scopes));
            $this->newLine();
            return $scopes;
        }

        $scopes = FortnoxScope::getDefaultScopes();
        $selectedScopes = ['profile', 'companyinformation']; // Always required

        $this->info('Selecting API scopes:');
        $this->info('Profile and companyinformation scope is always required and automatically included.');
        $this->newLine();

        foreach ($scopes as $scope => $default) {
            if ($scope === 'profile' || $scope === 'companyinformation') continue;

            $description = FortnoxScope::getDescription($scope);
            if ($this->confirm("{$scope} - {$description}?", $default)) {
                $selectedScopes[] = $scope;
            }
        }

        return $selectedScopes;
    }

    protected function performOAuthFlow(string $clientId, string $clientSecret, string $environment, array $scopes)
    {
        $this->state = Str::random(40);

        $authUrl = $this->getAuthUrl($clientId, $scopes);

        $this->info("Starting OAuth flow for {$environment} environment...");
        $this->info('Visit this URL to authorize:');
        $this->info($authUrl);

        $code = $this->ask('Enter the authorization code received:');

        $this->exchangeCodeForTokens($code, $clientId, $clientSecret, $environment);
    }

    protected function getAuthUrl(string $clientId, array $scopes): string
    {
        return 'https://apps.fortnox.se/oauth-v1/auth?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => rtrim($this->callbackUrl, '/') . '/fortnox/callback',
            'scope' => implode(' ', $scopes),
            'state' => $this->state,
            'access_type' => 'offline',
            'response_type' => 'code',
        ]);
    }

    protected function exchangeCodeForTokens(string $code, string $clientId, string $clientSecret, string $environment)
    {
        $redirectUri = rtrim($this->callbackUrl, '/') . '/fortnox/callback';

        // Create Base64 credentials
        $credentials = base64_encode($clientId . ':' . $clientSecret);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $credentials,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post('https://apps.fortnox.se/oauth-v1/token', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ]);

            if (!$response->successful()) {
                $error = $response->json();
                throw new \Exception(
                    'Token exchange failed: ' .
                    ($error['error_description'] ?? $error['error'] ?? $response->body())
                );
            }

            $data = $response->json();

            if (!isset($data['access_token']) || !isset($data['refresh_token'])) {
                throw new \Exception('Invalid response format: ' . json_encode($data));
            }

            FortnoxCredential::create([
                'environment' => $environment,
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
            ]);

            $this->info('Tokens received and stored successfully!');

        } catch (\Exception $e) {
            $this->error('Full error details:');
            $this->error($e->getMessage());
            throw $e;
        }
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

    protected function getCredentials(): array
    {
        $currentClientId = config('larafort.client_id');
        $currentClientSecret = config('larafort.client_secret');

        $clientId = $currentClientId;
        $clientSecret = $currentClientSecret;

        if ($currentClientId) {
            if (!$this->confirm("Found existing Client ID: {$currentClientId}. Keep this value?", true)) {
                $clientId = $this->ask('What is your Fortnox Client ID?');
            }
        } else {
            $clientId = $this->ask('What is your Fortnox Client ID?');
        }

        if ($currentClientSecret) {
            if (!$this->confirm('Found existing Client Secret. Keep this value?', true)) {
                $clientSecret = $this->secret('What is your Fortnox Client Secret?');
            }
        } else {
            $clientSecret = $this->secret('What is your Fortnox Client Secret?');
        }

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];
    }
}
