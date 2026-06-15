<?php

namespace Tests\Feature;

use App\Enums\CommentStatus;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Rate limit на web-маршрутах: locale, avatar, comments.destroy.
 */
class WebMutationThrottleTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    /**
     * Подготавливает окружение теста.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    /**
     * test locale update is limited to thirty attempts per minute.
     */
    public function test_locale_update_is_limited_to_thirty_attempts_per_minute(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->post(route('locale.update'), ['locale' => 'en'])->assertRedirect();
        }
        $this->post(route('locale.update'), ['locale' => 'ru'])->assertStatus(429);
    }

    /**
     * test profile avatar store is limited to ten attempts per minute.
     */
    public function test_profile_avatar_store_is_limited_to_ten_attempts_per_minute(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $user->assignRole('user');
        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->post(route('profile.avatar.store'), ['avatar' => $file])->assertRedirect();
        }
        $this->actingAs($user)->post(route('profile.avatar.store'), ['avatar' => $file])->assertStatus(429);
    }

    /**
     * test comment destroy is limited to twenty attempts per minute.
     */
    public function test_comment_destroy_is_limited_to_twenty_attempts_per_minute(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $post = Post::factory()->for($user)->create(['slug' => 'throttle-comment-post']);
        $comment = PostComment::query()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'Комментарий для throttle',
            'status' => CommentStatus::Visible,
        ]);
        for ($i = 0; $i < 20; $i++) {
            $restored = PostComment::query()->create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'body' => 'Комментарий '.$i,
                'status' => CommentStatus::Visible,
            ]);
            $this->actingAs($user)->delete(route('posts.comments.destroy', [
                'postSlug' => $post->slug,
                'commentId' => $restored->id,
            ]))->assertRedirect();
        }
        $this->actingAs($user)->delete(route('posts.comments.destroy', [
            'postSlug' => $post->slug,
            'commentId' => $comment->id,
        ]))->assertStatus(429);
    }
}
