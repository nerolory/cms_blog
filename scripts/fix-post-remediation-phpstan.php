#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Исправляет @return и @property-read generics, испорченные массовым PHPDoc-рефакторингом.
 */
$root = realpath(__DIR__.'/..');

if ($root === false) {
    exit(2);
}

$replacements = [
    'app/Enums/UserTheme.php' => [
        '/@return array<string, mixed>/' => '@return list<string>',
    ],
    'app/Enums/ReactionType.php' => [
        '/@return array<string, mixed>\s*\n(\s*\*\/\s*\n\s*public static function all\(\))/s' => "@return list<self>\n$1",
    ],
    'app/Enums/SiteHealthProfile.php' => [
        '/@return array<string, mixed>\s*\n(\s*\*\/\s*\n\s*public static function checkNames\(\))/s' => "@return list<string>\n$1",
    ],
    'app/DTO/PostAiInsights.php' => [
        '/@property-read Collection \$items/' => '@property-read Collection<int, AiInsightItem> $items',
    ],
    'app/DTO/SearchFilters.php' => [
        '/@property-read Collection \$tagIds/' => '@property-read Collection<int, int> $tagIds',
    ],
    'app/DTO/SiteOperationalStatus.php' => [
        '/@property-read Collection \$checks/' => '@property-read Collection<int, SiteOperationalCheck> $checks',
    ],
    'app/Filament/Resources/Posts/Pages/CreatePost.php' => [
        '/@return array<string, mixed>\s*\n(\s*\*\/\s*\n\s*protected function getHeaderActions\(\))/s' => "@return array<int, \\Filament\\Actions\\Action>\n$1",
    ],
    'app/Filament/Resources/Posts/Pages/EditPost.php' => [
        '/@return array<string, mixed>\s*\n(\s*\*\/\s*\n\s*protected function getHeaderActions\(\))/s' => "@return array<int, \\Filament\\Actions\\Action>\n$1",
    ],
    'app/Filament/Resources/Posts/Schemas/PostSeoSection.php' => [
        '/@return array<string, mixed>\s*\n(\s*\*\/\s*\n\s*public static function components\(\))/s' => "@return array<int, \\Filament\\Schemas\\Components\\Section>\n$1",
    ],
    'app/Filament/Widgets/ModerationSlaWidget.php' => [
        '/@return array<string, mixed>\s*\n(\s*\*\/\s*\n\s*protected function getStats\(\))/s' => "@return array<int, \\Filament\\Widgets\\StatsOverviewWidget\\Stat>\n$1",
    ],
];

$descriptionFixes = [
    '/(\n\s+\* )handle\.(\n\s+\* @return)/' => '$1Обрабатывает HTTP-запрос или задачу.$2',
    '/(\n\s+\* )approve\.(\n)/' => '$1Утверждает пост модератором.$2',
    '/(\n\s+\* )reject\.(\n)/' => '$1Отклоняет пост с указанием причины.$2',
    '/(\n\s+\* )destroy\.(\n)/' => '$1Удаляет пост из хранилища.$2',
    '/(\n\s+\* )restore\.(\n)/' => '$1Восстанавливает удалённый пост.$2',
];

$fixed = 0;

foreach ($replacements as $relativePath => $patterns) {
    $path = $root.'/'.$relativePath;

    if (! is_file($path)) {
        continue;
    }

    $source = file_get_contents($path);

    if ($source === false) {
        continue;
    }

    $original = $source;

    foreach ($patterns as $pattern => $replacement) {
        $source = preg_replace($pattern, $replacement, $source) ?? $source;
    }

    if ($source !== $original) {
        file_put_contents($path, $source);
        $fixed++;
    }
}

foreach (glob($root.'/app/{Http/Middleware,Jobs,Listeners}/**/*.php', GLOB_BRACE) ?: [] as $path) {
    $source = file_get_contents($path);

    if ($source === false || ! str_contains($source, '* handle.')) {
        continue;
    }

    $original = $source;

    foreach ($descriptionFixes as $pattern => $replacement) {
        $source = preg_replace($pattern, $replacement, $source) ?? $source;
    }

    if ($source !== $original) {
        file_put_contents($path, $source);
        $fixed++;
    }
}

$pipeline = $root.'/app/Services/Post/PostMutationPipeline.php';

if (is_file($pipeline)) {
    $source = file_get_contents($pipeline);
    $original = $source;

    foreach ($descriptionFixes as $pattern => $replacement) {
        $source = preg_replace($pattern, $replacement, $source) ?? $source;
    }

    if ($source !== $original) {
        file_put_contents($pipeline, $source);
        $fixed++;
    }
}

echo "Fixed PHPDoc generics/descriptions in {$fixed} file(s).\n";
