<?php

namespace JacobTilly\LaraFort\Tests\Feature;

use JacobTilly\LaraFort\Tests\TestCase;
use JacobTilly\LaraFort\Models\FortnoxCredential;
use JacobTilly\LaraFort\Facades\LaraFort;
use Illuminate\Support\Facades\Http;

class LaraFortTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');

        // Create test credentials
        FortnoxCredential::create([
            'environment' => 'test',
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires_at' => now()->addHour(),
        ]);
    }

    public function test_can_make_api_request()
    {
        Http::fake([
            'api.fortnox.se/3/*' => Http::response(['data' => 'test'], 200),
        ]);

        $response = LaraFort::get('customers');

        $this->assertEquals(['data' => 'test'], $response);
    }

    public function test_refreshes_expired_token()
    {
        FortnoxCredential::query()->update([
            'expires_at' => now()->subHour(),
        ]);

        Http::fake([
            'apps.fortnox.se/oauth-v1/token' => Http::response([
                'access_token' => 'new-access-token',
                'refresh_token' => 'new-refresh-token',
            ], 200),
            'api.fortnox.se/3/*' => Http::response(['data' => 'test'], 200),
        ]);

        $response = LaraFort::get('customers');

        $this->assertEquals(['data' => 'test'], $response);
        $this->assertDatabaseHas('fortnox_credentials', [
            'access_token' => 'new-access-token',
        ]);
    }
}
