<?php

namespace App\Support;

use App\Enums\PostEditorMode;
use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Sanitizes HTML for post body storage and display.
 */
final class HtmlSanitizer
{
    private const SIMPLE_TAGS = '<p><br><strong><b><em><i><ul><ol><li><a><h2><h3><h4><blockquote><img>'
        .'<figure><figcaption><details><summary><div><pre><code>';

    private const PRO_TAGS = '<p><br><strong><b><em><i><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote>'
        .'<img><div><span><table><thead><tbody><tr><th><td><pre><code><hr><figure><figcaption><details><summary>';

    private const BLOCK_CLASSES = ['details' => ['mce-accordion', 'post-spoiler'],
        'summary' => ['mce-accordion-summary', 'post-spoiler__title'], 'div' => ['mce-accordion-body',
            'post-spoiler__body'], 'blockquote' => ['post-quote'], 'pre' => ['line-numbers', 'post-code-block']];

    private const SIMPLE_ATTRS = ['a' => ['href', 'title', 'target', 'rel'], 'img' => ['src', 'alt', 'title', 'width',
        'height', 'class'], 'details' => ['class', 'open'], 'summary' => ['class'], 'div' => ['class'],
        'blockquote' => ['class'], 'pre' => ['class'], 'code' => ['class']];

    private const PRO_ATTRS = ['a' => ['href', 'title', 'target', 'rel', 'class', 'style'], 'img' => ['src', 'alt',
        'title', 'width', 'height', 'class', 'style'], 'div' => ['class', 'style'], 'span' => ['class', 'style'],
        'p' => ['class', 'style'], 'h1' => ['class', 'style'], 'h2' => ['class', 'style'], 'h3' => ['class', 'style'],
        'h4' => ['class', 'style'], 'h5' => ['class', 'style'], 'h6' => ['class', 'style'], 'table' => ['class',
            'style'], 'thead' => ['class', 'style'], 'tbody' => ['class', 'style'], 'tr' => ['class', 'style'],
        'th' => ['class', 'style', 'colspan', 'rowspan'], 'td' => ['class', 'style', 'colspan', 'rowspan'],
        'blockquote' => ['class', 'style'], 'pre' => ['class', 'style'], 'code' => ['class', 'style'],
        'details' => ['class', 'open'], 'summary' => ['class'], 'figure' => ['class', 'style'],
        'figcaption' => ['class', 'style'], 'ul' => ['class', 'style'], 'ol' => ['class', 'style'], 'li' => ['class',
            'style']];

    /**
     * Strip unsafe tags and normalize whitespace.

     *
     * @return string
     */
    public static function sanitize(string $html, PostEditorMode $mode = PostEditorMode::Simple): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }
        [$html, $preBlocks] = self::extractPreBlocks($html);
        $html = self::removeForbiddenBlocks($html);
        $html = self::scrubDangerousMarkup($html);
        $allowedTags = $mode === PostEditorMode::Pro ? self::PRO_TAGS : self::SIMPLE_TAGS;
        $clean = strip_tags($html, $allowedTags);
        $clean = self::scrubEventHandlers($clean);
        $clean = self::filterAttributes($clean, $mode === PostEditorMode::Pro ? self::PRO_ATTRS : self::SIMPLE_ATTRS);
        $clean = self::removeForbiddenElements($clean);
        $clean = self::filterImageSources($clean);
        $clean = self::normalizeWhitespace($clean);
        $clean = self::restorePreBlocks($clean, $preBlocks, $mode);

        return trim($clean);
    }

    /**
     * @return array{0: string, 1: array<string, string>}
     */
    private static function extractPreBlocks(string $html): array
    {
        $blocks = [];
        $protected = preg_replace_callback('/<pre\b[^>]*>.*?<\/pre>/is',
            function (array $matches) use (&$blocks): string {
                $token = '%%PREBLOCK'.count($blocks).'%%';
                $blocks[$token] = $matches[0];

                return $token;
            }, $html) ?? $html;

        return [$protected, $blocks];
    }

    /**
     * @param  array<string, string>  $blocks
     */
    private static function restorePreBlocks(string $html, array $blocks, PostEditorMode $mode): string
    {
        foreach ($blocks as $token => $block) {
            $html = str_replace($token, self::sanitizePreBlock($block, $mode), $html);
        }

        return $html;
    }

    /**
     * Санитизирует сохранённый блок &lt;pre&gt; перед вставкой в
     * итоговый HTML.
     */
    private static function sanitizePreBlock(string $block, PostEditorMode $mode): string
    {
        if (! preg_match('/^<pre\b([^>]*)>(.*)<\/pre>$/is', $block, $matches)) {
            return '';
        }
        $attrs = self::filterPreAttributes($matches[1], $mode);
        $inner = $matches[2];
        $inner = self::removeForbiddenBlocks($inner);
        $inner = self::scrubDangerousMarkup($inner);
        if (preg_match('/<code\b[^>]*>(.*)<\/code>/is', $inner, $codeMatch)) {
            $text = $codeMatch[1];
        } else {
            $text = strip_tags($inner);
        }
        $text = self::removeForbiddenBlocks($text);
        $text = self::scrubDangerousMarkup($text);
        $text = preg_replace('/<[^>]+>/', '', $text) ?? $text;
        $text = str_replace(['<', '>'], ['&lt;', '&gt;'], $text);

        return '<pre'.$attrs.'><code>'.$text.'</code></pre>';
    }

    private static function filterPreAttributes(string $rawAttrs, PostEditorMode $mode): string
    {
        $allowedClasses = self::BLOCK_CLASSES['pre'];
        if (! preg_match('/\bclass=(["\'])([^"\']*)\1/i', $rawAttrs, $classMatch)) {
            return '';
        }
        $classes = array_filter(preg_split('/\s+/', $classMatch[2]) ?: [],
            static fn (string $class): bool => in_array($class, $allowedClasses, true));
        if ($classes === []) {
            return '';
        }

        return ' class="'.implode(' ', $classes).'"';
    }

    private static function normalizeWhitespace(string $html): string
    {
        $html = preg_replace('/[ \t]+/u', ' ', $html) ?? $html;
        $html = preg_replace('/\n{3,}/u', "\n\n", $html) ?? $html;

        return $html;
    }

    /**
     * Removes executable or document-level markup blocks entirely.
     */
    private static function removeForbiddenBlocks(string $html): string
    {
        $patterns = ['/<script\b[^>]*>.*?<\/script>/is', '/<style\b[^>]*>.*?<\/style>/is',
            '/<noscript\b[^>]*>.*?<\/noscript>/is', '/<iframe\b[^>]*>.*?<\/iframe>/is',
            '/<object\b[^>]*>.*?<\/object>/is', '/<embed\b[^>]*\/?>/is', '/<link\b[^>]*\/?>/is', '/<meta\b[^>]*\/?>/is',
            '/<base\b[^>]*\/?>/is', '/<!--.*?-->/s'];
        foreach ($patterns as $pattern) {
            $html = preg_replace($pattern, '', $html) ?? $html;
        }

        return $html;
    }

    private static function scrubDangerousMarkup(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>/i', '', $html) ?? $html;
        $html = preg_replace('/<\/script>/i', '', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>/i', '', $html) ?? $html;
        $html = preg_replace('/<\/style>/i', '', $html) ?? $html;

        return $html;
    }

    private static function removeForbiddenElements(string $html): string
    {
        if (! class_exists(DOMDocument::class)) {
            return $html;
        }
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $wrapped = '<?xml encoding="UTF-8"><div>'.$html.'</div>';
        $document->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $root = $document->getElementsByTagName('div')->item(0);
        if (! $root instanceof DOMElement) {
            return $html;
        }
        self::removeForbiddenNodes($root);
        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return $result;
    }

    private static function removeForbiddenNodes(DOMNode $node): void
    {
        $forbidden = ['script', 'style', 'iframe', 'object', 'embed', 'link', 'meta', 'base', 'noscript'];
        $toRemove = [];
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                if ($child instanceof DOMElement && in_array(strtolower($child->tagName), $forbidden, true)) {
                    $toRemove[] = $child;
                } else {
                    self::removeForbiddenNodes($child);
                }
            }
        }
        foreach ($toRemove as $child) {
            $child->parentNode?->removeChild($child);
        }
    }

    private static function scrubEventHandlers(string $html): string
    {
        $html = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/iu', '', $html) ?? $html;
        $unsafeUrlPattern = '/\s(href|src|xlink:href|formaction|action|data|poster)\s*=\s*(["\'])'
            .'\s*(?:javascript|vbscript|data:text\/html)[^"\']*\2/iu';
        $html = preg_replace($unsafeUrlPattern, '', $html) ?? $html;
        $html = preg_replace('/\bsrcdoc\s*=\s*(["\']).*?\1/iu', '', $html) ?? $html;

        return $html;
    }

    /**
     * @param  array<string, list<string>>  $allowedByTag
     */
    private static function filterAttributes(string $html, array $allowedByTag): string
    {
        if (! class_exists(DOMDocument::class)) {
            return $html;
        }
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $wrapped = '<?xml encoding="UTF-8"><div>'.$html.'</div>';
        $document->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $root = $document->getElementsByTagName('div')->item(0);
        if (! $root instanceof DOMElement) {
            return $html;
        }
        self::walkAndFilterAttributes($root, $allowedByTag);
        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return $result;
    }

    /**
     * @param  array<string, list<string>>  $allowedByTag
     */
    private static function walkAndFilterAttributes(DOMNode $node, array $allowedByTag): void
    {
        if ($node instanceof DOMElement) {
            $tag = strtolower($node->tagName);
            $allowed = $allowedByTag[$tag] ?? [];
            if ($allowed === []) {
                while ($node->attributes->length > 0) {
                    $attribute = $node->attributes->item(0);
                    if ($attribute !== null) {
                        $node->removeAttributeNode($attribute);
                    }
                }
            } else {
                $toRemove = [];
                foreach ($node->attributes as $attribute) {
                    $name = strtolower($attribute->name);
                    if (str_starts_with($name, 'data-mce-')) {
                        $toRemove[] = $name;

                        continue;
                    }
                    if (! in_array($name, $allowed, true)) {
                        $toRemove[] = $name;
                    }
                }
                foreach ($toRemove as $name) {
                    $node->removeAttribute($name);
                }
                if ($node->hasAttribute('class')) {
                    $class = self::sanitizeClassAttribute($tag, $node->getAttribute('class'));
                    if ($class === '') {
                        $node->removeAttribute('class');
                    } else {
                        $node->setAttribute('class', $class);
                    }
                }
                if ($node->hasAttribute('href')) {
                    $href = trim($node->getAttribute('href'));
                    if ($href === '' || preg_match('/^\s*javascript:/i', $href)) {
                        $node->removeAttribute('href');
                    }
                }
                if ($tag === 'a') {
                    self::hardenBlankTargetAnchor($node);
                }
                if ($node->hasAttribute('style')) {
                    $style = self::sanitizeStyle($node->getAttribute('style'));
                    if ($style === '') {
                        $node->removeAttribute('style');
                    } else {
                        $node->setAttribute('style', $style);
                    }
                }
            }
        }
        if ($node->hasChildNodes()) {
            $children = [];
            foreach ($node->childNodes as $child) {
                $children[] = $child;
            }
            foreach ($children as $child) {
                self::walkAndFilterAttributes($child, $allowedByTag);
            }
        }
    }

    /**
     * Добавляет noopener/noreferrer для ссылок с target="_blank".
     */
    private static function hardenBlankTargetAnchor(DOMElement $node): void
    {
        if (strtolower($node->getAttribute('target')) !== '_blank') {
            return;
        }
        $rel = strtolower(trim($node->getAttribute('rel')));
        $tokens = array_values(array_filter(preg_split('/\s+/', $rel) ?: []));
        foreach (['noopener', 'noreferrer'] as $token) {
            if (! in_array($token, $tokens, true)) {
                $tokens[] = $token;
            }
        }
        $node->setAttribute('rel', implode(' ', $tokens));
    }

    private static function sanitizeStyle(string $style): string
    {
        $parts = array_filter(array_map('trim', explode(';', $style)));
        $safe = [];
        foreach ($parts as $part) {
            if (! str_contains($part, ':')) {
                continue;
            }
            [$property, $value] = array_map('trim', explode(':', $part, 2));
            $property = strtolower($property);
            if (preg_match('/expression|javascript|vbscript|@import|url\s*\(|\\\\url/i', $value)) {
                continue;
            }
            if (preg_match('/^(behavior|-moz-binding)$/i', $property)) {
                continue;
            }
            $allowedProperties = '/^(color|background-color|text-align|font-weight|font-style|'
                .'text-decoration|margin|padding|border|width|height|max-width|display|line-height)$/';
            if (preg_match($allowedProperties, $property)) {
                $safe[] = $property.': '.$value;
            }
        }

        return implode('; ', $safe);
    }

    private static function sanitizeClassAttribute(string $tag, string $classValue): string
    {
        $classes = preg_split('/\s+/', trim($classValue)) ?: [];
        $safe = [];
        foreach ($classes as $class) {
            if ($class === '') {
                continue;
            }
            if (self::isAllowedClass($tag, $class)) {
                $safe[] = $class;
            }
        }

        return implode(' ', array_unique($safe));
    }

    private static function isAllowedClass(string $tag, string $class): bool
    {
        if (str_starts_with($class, 'language-')) {
            return in_array($tag, ['pre', 'code'], true);
        }
        $allowed = self::BLOCK_CLASSES[$tag] ?? null;
        if ($allowed === null) {
            return false;
        }

        return in_array($class, $allowed, true);
    }

    private static function filterImageSources(string $html): string
    {
        if (! class_exists(DOMDocument::class) || ! str_contains($html, '<img')) {
            return $html;
        }
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $wrapped = '<?xml encoding="UTF-8"><div>'.$html.'</div>';
        $document->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $images = $document->getElementsByTagName('img');
        $toRemove = [];
        foreach ($images as $image) {
            if (! $image instanceof DOMElement) {
                continue;
            }
            $src = trim($image->getAttribute('src'));
            if (! self::isAllowedImageSource($src)) {
                $toRemove[] = $image;
            }
        }
        foreach ($toRemove as $image) {
            $image->parentNode?->removeChild($image);
        }
        $root = $document->getElementsByTagName('div')->item(0);
        if (! $root instanceof DOMElement) {
            return $html;
        }
        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return $result;
    }

    /**
     * Проверяет allowed image source.

     *
     * @return bool
     */
    public static function isAllowedImageSource(string $src): bool
    {
        if ($src === '') {
            return false;
        }
        if (str_starts_with($src, '/storage/')) {
            return true;
        }
        $storageUrl = rtrim(TypeCast::string(config('app.url')), '/').'/storage/';

        return str_starts_with($src, $storageUrl);
    }

    /**
     * Sanitize plain text for comments (no HTML).

     *
     * @return string
     */
    public static function sanitizePlainText(string $text): string
    {
        $text = strip_tags(trim($text));
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }
}
