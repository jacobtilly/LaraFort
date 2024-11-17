<?php

namespace JacobTilly\LaraFort\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use JacobTilly\LaraFort\Models\FortnoxCredential;

class LaraFortService
{
    protected $baseUrl = 'https://api.fortnox.se/3/';
    protected $tunnelProcess;

    public function get(string $endpoint, array $params = [])
    {
        return $this->request('GET', $endpoint, $params);
    }

    public function post(string $endpoint, array $data)
    {
        return $this->request('POST', $endpoint, $data);
    }

    public function put(string $endpoint, array $data)
    {
        return $this->request('PUT', $endpoint, $data);
    }

    public function delete(string $endpoint)
    {
        return $this->request('DELETE', $endpoint);
    }

    protected function request(string $method, string $endpoint, array $data = [])
    {
        $credentials = $this->getValidCredentials();

        return Http::withToken($credentials->access_token)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->{strtolower($method)}($this->baseUrl . ltrim($endpoint, '/'), $data)
            ->throw()
            ->json();
    }

    protected function getValidCredentials(): FortnoxCredential
    {
        $credentials = FortnoxCredential::where('environment', config('larafort.environment'))
            ->latest()
            ->first();

        if (!$credentials) {
            throw new \Exception('No Fortnox credentials found. Run: php artisan larafort:install');
        }

        if ($credentials->expires_at->subMinutes(5)->isPast()) {
            $credentials = $this->refreshTokens($credentials);
        }

        return $credentials;
    }

    public function refreshTokens(FortnoxCredential $credentials): FortnoxCredential
    {
        $encodedCredentials = base64_encode(
            config('larafort.client_id') . ':' . config('larafort.client_secret')
        );

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $encodedCredentials,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post('https://apps.fortnox.se/oauth-v1/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $credentials->refresh_token,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to refresh tokens. Please re-authenticate: php artisan larafort:install');
        }

        $data = $response->json();

        return FortnoxCredential::create([
            'environment' => $credentials->environment,
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
        ]);
    }


    public function startTunnel(): string
    {
        if ($this->tunnelProcess) {
            $this->stopTunnel();
        }

        $this->tunnelProcess = Process::start('herd share');

        // Wait for tunnel to establish and capture output
        $maxAttempts = 10;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $output = $this->tunnelProcess->latestOutput();

            if (preg_match('/https:\/\/([a-zA-Z0-9-]+)\.sharedwithexpose\.com/', $output, $matches)) {
                return $matches[0];
            }

            sleep(1);
            $attempts++;
        }

        $this->stopTunnel();
        throw new \Exception('Failed to start tunnel or capture URL');
    }

    public function stopTunnel(): void
    {
        if ($this->tunnelProcess && $this->tunnelProcess->isRunning()) {
            $this->tunnelProcess->stop(0, SIGINT); // Send SIGINT to stop the process
        }
    }
}
