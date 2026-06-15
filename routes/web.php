<?php

/**
 * Web-маршруты.
 *
 * Структура и правила binding: docs/ROUTING.md
 * Вариант A: в контроллерах — примитивы (postSlug, postId), загрузка через сервис → репозиторий.
 *
 * Параметр {postSlug} в URL — slug поста (вариант A, без model binding).
 */

use App\Http\Controllers\AiAnalysisOrderController;
use App\Http\Controllers\Auth\AccountPendingController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostImageUploadController;
use App\Http\Controllers\PostPreviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TokenWalletController;
use App\Http\Middleware\EnsureHealthEndpointAccess;
use Illuminate\Support\Facades\Route;

// --- Секция 1: Система (публично) ---

Route::get('/', fn () => view('welcome'))->name('home');

Route::get('health', HealthController::class)->middleware(EnsureHealthEndpointAccess::class)->name('health');

Route::post('locale', [LocaleController::class, 'update'])
    ->middleware('throttle:30,1')
    ->name('locale.update');

// --- Секция 2: Гость — вход, регистрация, сброс пароля ---

Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1');
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->middleware('throttle:5,1');
    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

// --- Секция 3: Аутентифицированный — logout, верификация email, pending ---

Route::middleware('auth')->group(function (): void {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('account/pending', [AccountPendingController::class, 'show'])
        ->name('account.pending');
});

// --- Секция 4: Профиль и токены (auth + account.active) ---

Route::middleware(['auth', 'account.active'])->group(function (): void {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('profile.update');
    Route::post('profile/avatar', [ProfileController::class, 'storeAvatar'])
        ->middleware('throttle:10,1')
        ->name('profile.avatar.store');
    Route::delete('profile/avatar', [ProfileController::class, 'destroyAvatar'])
        ->middleware('throttle:10,1')
        ->name('profile.avatar.destroy');

    Route::get('tokens', [TokenWalletController::class, 'show'])->name('tokens.show');
    // {package} — id пакета; F3: {packageId} + TokenWalletService
    Route::post('tokens/packages/{packageId}', [TokenWalletController::class, 'purchase'])
        ->middleware('throttle:10,1')
        ->name('tokens.packages.purchase');
});

// --- Секция 5: Публичные фиды, поиск, авторы, SEO ---

Route::get('feed/rss', [FeedController::class, 'rss'])->name('feed.rss');
Route::get('feed/atom', [FeedController::class, 'atom'])->name('feed.atom');
Route::get('feed.xml', [FeedController::class, 'rss'])->name('feed.xml');

Route::get('search', [SearchController::class, 'index'])->name('search.index');

// {user} — id автора; F3: {authorId} + AuthorService
Route::get('authors/{authorId}', [AuthorController::class, 'show'])->name('authors.show');

Route::get('sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('robots.txt', [SeoController::class, 'robots'])->name('seo.robots');

// --- Секция 6: Посты — коллекция (список) ---

Route::get('posts', [PostController::class, 'index'])
    ->middleware('conditional.get')
    ->name('posts.index');

// --- Секция 7: Посты — preview по signed token ---

Route::get('posts/preview/{token}', [PostPreviewController::class, 'show'])
    ->middleware(['signed', 'auth', 'account.active', 'preview.no-cache', 'throttle:30,1'])
    ->name('posts.preview.show');

// --- Секция 8: Посты — member + nested (auth + account.active) ---
// {postSlug} — slug поста (вариант A, без model binding).

Route::middleware(['auth', 'account.active'])->group(function (): void {
    // Nested: engagement
    Route::post('posts/{postSlug}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('posts.comments.store');
    Route::post('posts/{postSlug}/ai-analysis', [AiAnalysisOrderController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('posts.ai-analysis.store');
    Route::delete('posts/{postSlug}/comments/{commentId}', [CommentController::class, 'destroy'])
        ->middleware('throttle:20,1')
        ->name('posts.comments.destroy');
    Route::post('posts/{postSlug}/reactions', [ReactionController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('posts.reactions.store');

    // Коллекция: создание
    Route::get('posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('posts', [PostController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('posts.store');
    Route::post('posts/preview', [PostPreviewController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('posts.preview.store');
    Route::post('posts/images', [PostImageUploadController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('posts.images.store');

    // Member: CRUD одного поста (slug)
    Route::get('posts/{postSlug}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::post('posts/{postSlug}/preview', [PostPreviewController::class, 'storeForPost'])
        ->middleware('throttle:10,1')
        ->name('posts.preview.store.existing');
    Route::match(['put', 'patch'], 'posts/{postSlug}', [PostController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('posts.update');
    Route::delete('posts/{postSlug}', [PostController::class, 'destroy'])
        ->middleware('throttle:10,1')
        ->name('posts.destroy');
});

// --- Секция 9: Посты — публичный show (гость и auth, conditional GET) ---
// Регистрируется после auth-маршрутов, чтобы не перехватывать create/edit.

Route::get('posts/{postSlug}', [PostController::class, 'show'])
    ->middleware('conditional.get')
    ->name('posts.show');
