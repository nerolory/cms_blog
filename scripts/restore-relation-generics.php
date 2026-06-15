#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Восстанавливает generics @return на Eloquent-связях после AST-реформатирования.
 */
$root = realpath(__DIR__.'/../app/Models');

if ($root === false) {
    exit(2);
}

$fixed = 0;

foreach (glob($root.'/*.php') ?: [] as $path) {
    $source = file_get_contents($path);

    if ($source === false) {
        continue;
    }

    $original = $source;

    $source = preg_replace_callback(
        '/(\/\*\*(?:.*?\n\s*)*?\* @return )([A-Za-z\\\\]+)(\s*\n\s*\*\/\s*\n\s*public function \w+\(\): \2\s*\{\s*\n\s*return \$this->(?:belongsTo|hasMany|hasOne|belongsToMany)\((?:\\\\?[A-Za-z\\\\]*\\\\)?([A-Za-z]+)::class(?:[^;\n]*)?;)/s',
        static function (array $m): string {
            $relation = $m[2];
            $related = $m[4];

            return $m[1].$relation.'<'.$related.', $this>'.$m[3];
        },
        $source,
    ) ?? $source;

    $source = preg_replace_callback(
        '/(\/\*\*(?:.*?\n\s*)*?\* @return )MorphTo(\s*\n\s*\*\/\s*\n\s*public function \w+\(\): MorphTo)/s',
        static fn (array $m): string => $m[1].'MorphTo<Model, $this>'.$m[2],
        $source,
    ) ?? $source;

    if ($source !== $original) {
        file_put_contents($path, $source);
        $fixed++;
        echo "Fixed relations: {$path}\n";
    }
}

echo "Restored relation generics in {$fixed} model file(s).\n";
