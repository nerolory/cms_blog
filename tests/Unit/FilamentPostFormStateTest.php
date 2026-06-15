<?php

namespace Tests\Unit;

use App\DTO\FilamentPostFormState;
use App\Enums\PostEditorMode;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Класс filament post form state.
 */
class FilamentPostFormStateTest extends TestCase
{
    /**
     * it maps filament payload to post and seo dtos.
     */
    #[Test]
    public function it_maps_filament_payload_to_post_and_seo_dtos(): void
    {
        $state = FilamentPostFormState::fromArray(['title' => 'Test title', 'slug' => 'test-title',
            'excerpt' => 'Excerpt', 'body' => 'Body content long enough.',
            'editor_mode' => PostEditorMode::Simple->value, 'status' => PostStatus::Draft->value,
            'visibility' => PostVisibility::Guest->value, 'category_id' => 1, 'meta_title' => 'Meta',
            'meta_description' => 'Description']);
        $postData = $state->toPostData();
        $seoData = $state->toSeoData();
        $this->assertSame('Test title', $postData->title);
        $this->assertSame(PostStatus::Draft, $postData->status);
        $this->assertSame('Meta', $seoData->metaTitle);
    }
}
