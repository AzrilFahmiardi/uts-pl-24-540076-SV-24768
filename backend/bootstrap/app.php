<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Controllers\HealthController;
use App\Http\Middleware\AssignRequestId;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Tanpa middleware web (session/cookie) agar tetap bisa menjawab saat DB mati.
            Route::get('/health', HealthController::class)->name('health');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(AssignRequestId::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Laporan bawaan QueryException memuat SQL beserta binding (bisa berisi
        // data pribadi/token), jadi dicatat sendiri dengan konteks yang aman.
        $exceptions->dontReport(QueryException::class);

        $exceptions->render(function (QueryException $e, Request $request) {
            Log::error('Gagal menghubungi atau memakai basis data', [
                'event' => 'db.query_failed',
                'user_id' => $request->user()?->id,
                'action' => $request->method().' '.$request->path(),
                'sql_state' => $e->getCode(),
                'reason' => preg_replace('/\s*\(Connection:.*$/s', '', $e->getMessage()),
            ]);

            return response()->json(['message' => 'Layanan basis data sedang tidak tersedia.'], 503);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            // Hanya nama field yang dicatat, bukan nilainya.
            Log::warning('Validasi request gagal', [
                'event' => 'validation.failed',
                'user_id' => $request->user()?->id,
                'action' => $request->method().' '.$request->path(),
                'failed_fields' => array_keys($e->errors()),
            ]);

            return null; // lanjutkan ke respons 422 bawaan
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
