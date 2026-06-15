#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Добавляет @return / @param generics к методам репозиториев и сервисов (F5.2).
 */
$annotations = [
    'app/Repositories/Contracts/AiToolResultRepositoryContract.php' => [
        'getCompletedForPost' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\AiToolResult> */',
    ],
    'app/Repositories/Contracts/AuthorRepositoryContract.php' => [
        'getPublishedPosts' => '    /** @return \\Illuminate\\Pagination\\LengthAwarePaginator<int, \\App\\Models\\Post> */',
    ],
    'app/Repositories/Contracts/CategoryRepositoryContract.php' => [
        'allOrdered' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\Category> */',
    ],
    'app/Repositories/Contracts/CommentRepositoryContract.php' => [
        'getVisibleRootCommentsForPost' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\PostComment> */',
    ],
    'app/Repositories/Contracts/PostModerationLogRepositoryContract.php' => [
        'forPost' => '    /** @return \\Illuminate\\Database\\Eloquent\\Collection<int, \\App\\Models\\PostModerationLog> */',
        'record' => '    /** @param \\Illuminate\\Support\\Collection<int, mixed> $metadata */',
    ],
    'app/Repositories/Contracts/PostVersionRepositoryContract.php' => [
        'listForPost' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\PostVersion> */',
    ],
    'app/Repositories/Contracts/PostViewRepositoryContract.php' => [
        'flushPendingCounts' => '    /** @return \\Illuminate\\Support\\Collection<int, int> */',
    ],
    'app/Repositories/Contracts/ReactionRepositoryContract.php' => [
        'countsForPost' => '    /** @return \\Illuminate\\Support\\Collection<int, int> */',
    ],
    'app/Repositories/Contracts/SearchRepositoryContract.php' => [
        'search' => '    /** @return \\Illuminate\\Pagination\\LengthAwarePaginator<int, \\App\\Models\\Post> */',
    ],
    'app/Repositories/Contracts/SiteHealthReportRepositoryContract.php' => [
        'latest' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\SiteHealthReport> */',
    ],
    'app/Repositories/Contracts/SiteTemplateRepositoryContract.php' => [
        'getAll' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\SiteTemplate> */',
        'getThemeSlugs' => '    /** @return \\Illuminate\\Support\\Collection<int, string> */',
    ],
    'app/Repositories/Contracts/TagRepositoryContract.php' => [
        'allOrdered' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\Tag> */',
        'syncForPost' => '    /** @param \\Illuminate\\Support\\Collection<int, int> $tagIds */',
    ],
    'app/Repositories/Contracts/TokenPackageRepositoryContract.php' => [
        'listActive' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\TokenPackage> */',
    ],
    'app/Repositories/AiToolResultRepository.php' => [
        'getCompletedForPost' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\AiToolResult> */',
    ],
    'app/Repositories/AuthorRepository.php' => [
        'getPublishedPosts' => '    /** @return \\Illuminate\\Pagination\\LengthAwarePaginator<int, \\App\\Models\\Post> */',
    ],
    'app/Repositories/CategoryRepository.php' => [
        'allOrdered' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\Category> */',
    ],
    'app/Repositories/CommentRepository.php' => [
        'getVisibleRootCommentsForPost' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\PostComment> */',
    ],
    'app/Repositories/PostVersionRepository.php' => [
        'listForPost' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\PostVersion> */',
    ],
    'app/Repositories/PostViewRepository.php' => [
        'flushPendingCounts' => '    /** @return \\Illuminate\\Support\\Collection<int, int> */',
    ],
    'app/Repositories/ReactionRepository.php' => [
        'countsForPost' => '    /** @return \\Illuminate\\Support\\Collection<int, int> */',
    ],
    'app/Repositories/SearchRepository.php' => [
        'search' => '    /** @return \\Illuminate\\Pagination\\LengthAwarePaginator<int, \\App\\Models\\Post> */',
    ],
    'app/Repositories/SiteHealthReportRepository.php' => [
        'latest' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\SiteHealthReport> */',
    ],
    'app/Repositories/SiteTemplateRepository.php' => [
        'getAll' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\SiteTemplate> */',
        'getThemeSlugs' => '    /** @return \\Illuminate\\Support\\Collection<int, string> */',
    ],
    'app/Repositories/TagRepository.php' => [
        'allOrdered' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\Tag> */',
        'syncForPost' => '    /** @param \\Illuminate\\Support\\Collection<int, int> $tagIds */',
    ],
    'app/Repositories/TokenPackageRepository.php' => [
        'listActive' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\TokenPackage> */',
    ],
    'app/Repositories/Search/Contracts/SearchBackendContract.php' => [
        'search' => '    /** @return \\Illuminate\\Pagination\\LengthAwarePaginator<int, \\App\\Models\\Post> */',
    ],
    'app/Repositories/Search/AbstractExternalSearchBackend.php' => [
        'search' => '    /** @return \\Illuminate\\Pagination\\LengthAwarePaginator<int, \\App\\Models\\Post> */',
    ],
    'app/Repositories/Search/DatabaseSearchBackend.php' => [
        'search' => '    /** @return \\Illuminate\\Pagination\\LengthAwarePaginator<int, \\App\\Models\\Post> */',
    ],
    'app/Services/Contracts/AuthorServiceContract.php' => [
        'getPublishedPosts' => '    /** @return \\Illuminate\\Pagination\\LengthAwarePaginator<int, \\App\\Models\\Post> */',
    ],
    'app/Services/Contracts/CommentServiceContract.php' => [
        'getVisibleTreeForPost' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\PostComment> */',
    ],
    'app/Services/Contracts/MailSettingsServiceContract.php' => [
        'availablePresets' => '    /** @return \\Illuminate\\Support\\Collection<string, string> */',
    ],
    'app/Services/Contracts/ReactionServiceContract.php' => [
        'countsForPost' => '    /** @return \\Illuminate\\Support\\Collection<int, int> */',
    ],
    'app/Services/Contracts/SearchServiceContract.php' => [
        'search' => '    /** @return \\Illuminate\\Pagination\\LengthAwarePaginator<int, \\App\\Models\\Post> */',
    ],
    'app/Services/Contracts/SearchSettingsServiceContract.php' => [
        'availableDrivers' => '    /** @return \\Illuminate\\Support\\Collection<string, string> */',
    ],
    'app/Services/Contracts/SeoServiceContract.php' => [
        'httpCacheContextForListing' => '    /** @param \\Illuminate\\Pagination\\LengthAwarePaginator<int, \\App\\Models\\Post> $posts */',
    ],
    'app/Services/Contracts/SiteHealthServiceContract.php' => [
        'history' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\SiteHealthReport> */',
    ],
    'app/Services/Contracts/SiteTemplateServiceContract.php' => [
        'availableThemeSlugs' => '    /** @return \\Illuminate\\Support\\Collection<int, string> */',
    ],
    'app/Services/AuthorService.php' => [
        'getPublishedPosts' => '    /** @return \\Illuminate\\Pagination\\LengthAwarePaginator<int, \\App\\Models\\Post> */',
    ],
    'app/Services/CommentService.php' => [
        'getVisibleTreeForPost' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\PostComment> */',
    ],
    'app/Services/ReactionService.php' => [
        'countsForPost' => '    /** @return \\Illuminate\\Support\\Collection<int, int> */',
    ],
    'app/Services/SearchService.php' => [
        'search' => '    /** @return \\Illuminate\\Pagination\\LengthAwarePaginator<int, \\App\\Models\\Post> */',
    ],
    'app/Services/SearchSettingsService.php' => [
        'availableDrivers' => '    /** @return \\Illuminate\\Support\\Collection<string, string> */',
    ],
    'app/Services/SiteHealthService.php' => [
        'history' => '    /** @return \\Illuminate\\Support\\Collection<int, \\App\\Models\\SiteHealthReport> */',
    ],
    'app/Services/SiteTemplateService.php' => [
        'availableThemeSlugs' => '    /** @return \\Illuminate\\Support\\Collection<int, string> */',
    ],
];

$root = realpath(__DIR__.'/..');

if ($root === false) {
    exit(2);
}

$fixed = 0;

foreach ($annotations as $relativePath => $methods) {
    $path = $root.'/'.$relativePath;

    if (! is_file($path)) {
        continue;
    }

    $source = file_get_contents($path);

    if ($source === false) {
        continue;
    }

    $original = $source;

    foreach ($methods as $methodName => $docblock) {
        if (str_contains($source, $docblock)) {
            continue;
        }

        $pattern = '/(\n)(\s*(?:public|protected)\s+(?:static\s+)?function\s+'.preg_quote($methodName, '/').'\s*\()/';

        $source = preg_replace(
            $pattern,
            "\n{$docblock}\n$2",
            $source,
            1,
        ) ?? $source;
    }

    if ($source !== $original) {
        file_put_contents($path, $source);
        $fixed++;
    }
}

echo "Annotated generics in {$fixed} files.\n";
