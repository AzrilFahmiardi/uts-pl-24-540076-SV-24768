<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_returns_200_when_database_and_cache_are_ok(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJsonPath('status', 'healthy')
            ->assertJsonPath('checks.database.status', 'ok')
            ->assertJsonPath('checks.cache.status', 'ok');
    }

    public function test_health_returns_503_and_names_database_when_database_fails(): void
    {
        DB::shouldReceive('select')->andThrow(new \RuntimeException('koneksi ditolak'));

        $this->getJson('/health')
            ->assertStatus(503)
            ->assertJsonPath('status', 'unhealthy')
            ->assertJsonPath('checks.database.status', 'fail')
            ->assertJsonPath('failed.0', 'database');
    }

    public function test_health_returns_503_and_names_cache_when_cache_fails(): void
    {
        Cache::shouldReceive('put')->andThrow(new \RuntimeException('cache tidak tersedia'));

        $this->getJson('/health')
            ->assertStatus(503)
            ->assertJsonPath('checks.cache.status', 'fail')
            ->assertJsonPath('failed.0', 'cache');
    }

    public function test_failed_login_is_logged_without_password_or_email(): void
    {
        $captured = [];
        Log::listen(function ($log) use (&$captured) {
            $captured[] = json_encode([$log->level, $log->message, $log->context]);
        });

        $this->postJson('/api/login', ['email' => 'rahasia@contoh.id', 'password' => 'PasswordSangatRahasia'])
            ->assertUnauthorized();

        $this->assertNotEmpty($captured);
        $all = implode("\n", $captured);
        $this->assertStringContainsString('auth.login_failed', $all);
        $this->assertStringNotContainsString('PasswordSangatRahasia', $all);
        $this->assertStringNotContainsString('rahasia@contoh.id', $all);
    }
}
