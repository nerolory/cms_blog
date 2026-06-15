<?php

namespace Tests\Unit;

use App\DTO\PostData;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Exceptions\InvalidPostDataException;
use App\Models\Post;
use Tests\TestCase;

/**
 * Класс post data.
 */
class PostDataTest extends TestCase
{
    /**
     * Builds a DTO from valid input data.
     */
    public function test_from_validated_creates_dto(): void
    {
        $dto = PostData::fromValidated(title: '  Заголовок  ', slug: 'zagolovok',
            excerpt: 'Краткое превью поста',
            body: 'Текст поста достаточной длины', isPublished: true, userId: 1);
        $this->assertSame('Заголовок', $dto->title);
        $this->assertSame('zagolovok', $dto->slug);
        $this->assertTrue($dto->is_published);
        $this->assertSame(1, $dto->user_id);
    }

    /**
     * Throws when the title is empty after trim.
     */
    public function test_from_validated_throws_on_empty_title(): void
    {
        $this->expectException(InvalidPostDataException::class);
        PostData::fromValidated(title: '   ', slug: 'slug', excerpt: 'Превью', body: 'Текст поста',
            isPublished: false, userId: null);
    }

    /**
     * test from validated update keeps blank optional strings.
     */
    public function test_from_validated_update_keeps_blank_optional_strings(): void
    {
        $post = new Post;
        $post->forceFill(['slug' => 'keep-slug', 'excerpt' => 'Keep excerpt', 'user_id' => 5, 'category_id' => 1]);
        $dto = PostData::fromValidatedUpdate(title: 'New title', slug: '', excerpt: '   ', body: 'Body text here',
            isPublished: false, userId: null, status: PostStatus::Draft, visibility: PostVisibility::Guest->value,
            requiredPermissionId: null, existing: $post);
        $this->assertSame('keep-slug', $dto->slug);
        $this->assertSame('Keep excerpt', $dto->excerpt);
        $this->assertSame(5, $dto->user_id);
    }
}
