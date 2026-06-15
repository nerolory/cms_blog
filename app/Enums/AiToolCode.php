<?php

namespace App\Enums;

/**
 * Перечисление ai tool code.
 */
enum AiToolCode: string
{
    case CommentSummary = 'comment_summary';
    case ApprovalIndex = 'approval_index';
    case ArticleVsComments = 'article_vs_comments';
    case SemanticSearch = 'semantic_search';

    /**
     * label.

     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::CommentSummary => __('ai.tools.comment_summary'),
            self::ApprovalIndex => __('ai.tools.approval_index'),
            self::ArticleVsComments => __('ai.tools.article_vs_comments'),
            self::SemanticSearch => __('ai.tools.semantic_search'),
        };
    }
}
