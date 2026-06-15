<?php

namespace App\Support;

use App\Enums\PostEditorMode;
use Filament\Forms\Components\RichEditor\RichContentRenderer;

/**
 * Converts post body between Filament RichEditor (TipTap JSON) and HTML strings.
 */
final class FilamentPostBody
{
    /**
     * document to html.

     *
     * @return string
     */
    public static function documentToHtml(mixed $state): string
    {
        if (is_string($state)) {
            return $state;
        }
        if (is_array($state)) {
            /** @var array<string, mixed> $document */
            $document = $state;

            return RichContentRenderer::make($document)->toHtml();
        }

        return '';
    }

    /**
     * resolve from filament.
     *
     * @param  array<string, mixed>  $data
     * @return string
     */
    public static function resolveFromFilament(array $data, PostEditorMode $editorMode): string
    {
        if ($editorMode === PostEditorMode::Pro) {
            $html = $data['body_html'] ?? $data['body'] ?? '';

            return trim(self::documentToHtml($html));
        }

        return trim(self::documentToHtml($data['body'] ?? ''));
    }

    /**
     * prepare for fill.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function prepareForFill(array $data): array
    {
        $data['body_html'] = self::documentToHtml($data['body'] ?? '');

        return $data;
    }
}
