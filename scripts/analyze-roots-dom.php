<?php

declare(strict_types=1);
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$slug = $argv[1] ?? 'verify-engagement-post';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Request::create('/posts/'.$slug, 'GET'));
$html = (string) $response->getContent();

if (! preg_match('/data-comment-roots[^>]*>(.*)<div[^>]*data-comment-roots-sentinel/s', $html, $match)) {
    fwrite(STDERR, "Could not find data-comment-roots block\n");
    exit(1);
}

$inner = $match[1];
echo 'threads='.substr_count($inner, 'data-comment-thread').PHP_EOL;
echo 'branch='.substr_count($inner, 'post-comment-thread__branch').PHP_EOL;

preg_match_all('/<div[^>]*data-comment-replies[^>]*>(.*?)<\/div>/s', $inner, $blocks);
foreach ($blocks[1] as $index => $block) {
    $hasThread = str_contains($block, 'data-comment-thread') ? 'YES' : 'NO';
    echo "replies_block_{$index}_has_thread={$hasThread}".PHP_EOL;
}
