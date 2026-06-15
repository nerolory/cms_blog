<?php

namespace App\Support\Post;

use App\DTO\PostShowContentData;

/**
 * Сборка данных отображения тела поста для страницы show.
 */
final class PostShowContent
{
    /**
     * Формирует оглавление, HTML с id заголовков и время чтения.
     *
     * @return PostShowContentData
     */
    public static function fromBody(string $body): PostShowContentData
    {
        $tocResult = TableOfContentsExtractor::extractAndInjectIds($body);

        return new PostShowContentData(toc: $tocResult->entries, bodyWithToc: $tocResult->html,
            readingMinutes: ReadingTimeCalculator::minutes($body));
    }
}
