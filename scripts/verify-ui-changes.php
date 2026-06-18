<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$failures = [];

/** @var Illuminate\Contracts\Http\Kernel $kernel */
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$postsRequest = Illuminate\Http\Request::create('/posts', 'GET');
$postsResponse = $kernel->handle($postsRequest);
$postsBody = (string) $postsResponse->getContent();

if ($postsResponse->getStatusCode() !== 200) {
    $failures[] = 'POSTS_STATUS='.$postsResponse->getStatusCode();
}
if (! str_contains($postsBody, 'post-card__panel')) {
    $failures[] = 'POSTS_PANEL=NO';
}
if (! str_contains($postsBody, 'post-card__title')) {
    $failures[] = 'POSTS_TITLE_BG=NO';
}
$kernel->terminate($postsRequest, $postsResponse);

$purgeSource = (string) file_get_contents(__DIR__.'/../app/Repositories/ReactionRepository.php');
if (str_contains($purgeSource, "Schema::hasTable('cache')")) {
    $failures[] = 'REACTION_PURGE_HAS_SCHEMA=YES';
}

$cssFiles = glob(__DIR__.'/../public/build/assets/app-*.css') ?: [];
$cssHasPanel = false;
foreach ($cssFiles as $cssFile) {
    $css = (string) file_get_contents($cssFile);
    if (str_contains($css, 'post-card__panel') && str_contains($css, 'post-card__title')) {
        $cssHasPanel = true;
        break;
    }
}
if (! $cssHasPanel) {
    $failures[] = 'VITE_CSS_STYLES=NO (run: npm run build on Windows host)';
}

$adminTestExit = 0;
passthru(
    escapeshellarg(PHP_BINARY).' artisan test --filter=test_admin_topbar_shows_public_site_link 2>&1',
    $adminTestExit,
);
if ($adminTestExit !== 0) {
    $failures[] = 'ADMIN_LINK_TEST=FAIL';
}

if ($failures !== []) {
    fwrite(STDERR, "UI verify failed:\n- ".implode("\n- ", $failures)."\n");
    exit(1);
}

echo "UI verify OK\n";
echo "- posts markup: panel + title classes\n";
echo "- admin link: feature test passed\n";
echo "- reaction purge: no legacy schema SQL\n";
echo "- vite css: post-card styles in public/build\n";
