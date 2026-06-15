<?php

/**
 * API-маршруты (prefix /api).
 *
 * Структура: docs/ROUTING.md
 * Вариант A: int $postId в контроллере, без Post $post binding.
 *
 * Сейчас {postId} — числовой id + PostService (вариант A).
 */

use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

// --- API v1: посты ---

Route::prefix('v1')->group(function (): void {
    // Read: публичный список и show с conditional GET
    Route::middleware('conditional.get')->group(function (): void {
        Route::get('posts', [PostApiController::class, 'index']);
        Route::get('posts/{postId}', [PostApiController::class, 'show']);
    });

    // Write: Sanctum + отдельный лимит на мутации
    Route::middleware(['auth:sanctum', 'throttle:api-write'])->group(function (): void {
        Route::post('posts', [PostApiController::class, 'store']);
        Route::patch('posts/{postId}', [PostApiController::class, 'update']);
        Route::delete('posts/{postId}', [PostApiController::class, 'destroy']);
    });
});

// --- Webhooks (без Sanctum; подпись HMAC) ---

Route::post('webhooks/payment', [PaymentWebhookController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('webhooks.payment');
