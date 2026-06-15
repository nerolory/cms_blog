<?php

declare(strict_types=1);

/**
 * Рендер публичных страниц в HTML для W3C Nu Validator (тестовое окружение).
 */

use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

require __DIR__.'/../vendor/autoload.php';

final class W3cFixtureRenderer extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function bootstrapForW3c(): void
    {
        $this->refreshApplication();
        $this->refreshDatabase();
        Cache::flush();
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->fakeSiteOperational();
        $this->seedRoles();
    }

    /**
     * @return array<string, string>
     */
    public function renderAll(): array
    {
        $author = User::factory()->create(['name' => 'W3C Author']);
        $post = Post::factory()->for($author)->published()->create([
            'title' => 'W3C Fixture Post',
            'slug' => 'w3c-fixture-post',
            'excerpt' => 'Fixture excerpt for W3C validation of the public post page.',
            'body' => '<p>Fixture body paragraph.</p><h2 id="section-one">Section</h2><p>More text.</p>',
            'visibility' => PostVisibility::Guest->value,
        ]);

        return [
            'home' => $this->get('/')->getContent(),
            'login' => $this->get(route('login'))->getContent(),
            'posts-index' => $this->get(route('posts.index'))->getContent(),
            'post-show' => $this->get(route('posts.show', $post))->getContent(),
        ];
    }
}

$outputDir = __DIR__.'/../storage/framework/w3c-fixtures';
if (! is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$renderer = new W3cFixtureRenderer('w3c');
$renderer->bootstrapForW3c();

foreach ($renderer->renderAll() as $name => $html) {
    file_put_contents($outputDir.'/'.$name.'.html', $html);
    echo "rendered: {$name}.html (".strlen($html)." bytes)\n";
}
