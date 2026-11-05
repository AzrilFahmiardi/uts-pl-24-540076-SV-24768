<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check(fn () => $this->probeDatabase()),
            'cache' => $this->check(function () {
                $key = 'health:'.Str::random(8);
                Cache::put($key, 'ok', 10);

                if (Cache::get($key) !== 'ok') {
                    throw new \RuntimeException('nilai cache yang ditulis tidak dapat dibaca kembali');
                }

                Cache::forget($key);
            }),
        ];

        $failed = array_keys(array_filter($checks, fn ($c) => $c['status'] !== 'ok'));
        $healthy = $failed === [];

        if (! $healthy) {
            Log::error('Health check gagal', [
                'event' => 'health.check_failed',
                'failed_components' => $failed,
                'reasons' => array_map(fn ($name) => $checks[$name]['error'], $failed),
            ]);
        }

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'failed' => $failed,
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    /**
     * Untuk MySQL dipakai koneksi PDO baru dengan timeout pendek. Konektor
     * Laravel mengulang koneksi beberapa kali, sehingga saat host DB tidak
     * ada /health bisa menggantung lebih lama dari timeout healthcheck.
     */
    private function probeDatabase(): void
    {
        $config = config('database.connections.'.config('database.default'));

        if (($config['driver'] ?? null) !== 'mysql') {
            DB::select('select 1');

            return;
        }

        $pdo = new \PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
            $config['username'],
            $config['password'],
            [\PDO::ATTR_TIMEOUT => 2, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION],
        );
        $pdo->query('select 1');
    }

    private function check(callable $probe): array
    {
        $start = microtime(true);

        try {
            $probe();

            return ['status' => 'ok', 'latency_ms' => round((microtime(true) - $start) * 1000, 1)];
        } catch (Throwable $e) {
            return ['status' => 'fail', 'error' => $this->sanitize($e)];
        }
    }

    /** Ambil sebab singkat tanpa SQL, binding, atau kredensial. */
    private function sanitize(Throwable $e): string
    {
        $message = $e->getMessage();
        // Pesan QueryException berisi "(Connection: ..., SQL: ...)" - buang bagian itu.
        $message = preg_replace('/\s*\(Connection:.*$/s', '', $message);

        return Str::limit($message, 200);
    }
}
