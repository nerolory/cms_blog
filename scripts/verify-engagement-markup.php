<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use Illuminate\Support\Facades\Artisan;

Artisan::call('view:clear');

$author = User::query()->where('email', 'verify-engagement@example.com')->first();
if (! $author instanceof User) {
    $author = User::factory()->create([
        'email' => 'verify-engagement@example.com',
    ]);
}

$post = Post::query()->where('slug', 'verify-engagement-post')->first();
if (! $post instanceof Post) {
    $post = Post::factory()->for($author)->create([
        'slug' => 'verify-engagement-post',
        'status' => PostStatus::Published,
        'visibility' => PostVisibility::Guest,
        'published_at' => now(),
    ]);
}

PostComment::query()->where('post_id', $post->id)->delete();
PostComment::query()->create([
    'post_id' => $post->id,
    'user_id' => $author->id,
    'body' => 'Verify root A',
    'parent_id' => null,
    'status' => CommentStatus::Visible,
]);
PostComment::query()->create([
    'post_id' => $post->id,
    'user_id' => $author->id,
    'body' => 'Verify root B',
    'parent_id' => null,
    'status' => CommentStatus::Visible,
]);

try {
    app(App\Services\Contracts\CommentServiceContract::class)->forgetSectionCacheForPost($post->id);
} catch (\Throwable) {
}

/** @var Illuminate\Contracts\Http\Kernel $kernel */
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/posts/'.$post->slug, 'GET');
$response = $kernel->handle($request);
$body = (string) $response->getContent();

$failures = [];
if ($response->getStatusCode() !== 200) {
    $failures[] = 'STATUS='.$response->getStatusCode();
}
if (! str_contains($body, 'data-post-engagement-app')) {
    $failures[] = 'NO_ENGAGEMENT_APP';
}
if (! str_contains($body, 'data-comment-roots-sentinel')) {
    $failures[] = 'NO_ROOTS_SENTINEL';
}
if (! str_contains($body, 'comment-skeleton-template')) {
    $failures[] = 'NO_SKELETON_TEMPLATE';
}
$branchCount = substr_count($body, 'post-comment-thread__branch');
if ($branchCount < 2) {
    $failures[] = 'BRANCH_MARKUP='.$branchCount;
}
if (str_contains($body, 'data-comment-load-roots')) {
    $failures[] = 'HAS_LOAD_MORE_BUTTON';
}
if (! preg_match('/post-body-code-[^.]+\.js/', $body)) {
    $failures[] = 'NO_POST_BODY_JS';
}

$cssFile = glob(__DIR__.'/../public/build/assets/post-body-code-*.css')[0] ?? null;
if ($cssFile === null) {
    $failures[] = 'NO_POST_BODY_CSS';
} else {
    $css = (string) file_get_contents($cssFile);
    if (! str_contains($css, 'post-comment-thread__branch')) {
        $failures[] = 'POST_BODY_CSS_STALE';
    }
}

if ($failures !== []) {
    fwrite(STDERR, "Engagement verify failed:\n- ".implode("\n- ", $failures)."\n");
    exit(1);
}

$analyze = shell_exec('php '.escapeshellarg(__DIR__.'/analyze-roots-dom.php').' '.escapeshellarg($post->slug));
if (! is_string($analyze) || ! str_contains($analyze, 'threads=2') || ! str_contains($analyze, 'branch=2')) {
    fwrite(STDERR, "Engagement verify failed:\n- ROOTS_DOM\n{$analyze}\n");
    exit(1);
}

echo "Engagement verify OK (post: {$post->slug})\n";
