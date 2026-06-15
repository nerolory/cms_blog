<?php

namespace App\Support\Media;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Оптимизация JPEG/PNG, уже лежащих в public disk.
 */
final class StoredImageOptimizer
{
    private const JPEG_QUALITY = 85;

    /**
     * optimize at path.

     *
     * @return ?string
     */
    public function optimizeAtPath(string $storagePath, int $maxWidth): ?string
    {
        if (! Storage::disk('public')->exists($storagePath)) {
            return null;
        }
        if (! $this->gdSupportsImages()) {
            return null;
        }
        $absolute = Storage::disk('public')->path($storagePath);
        if (! is_readable($absolute)) {
            return null;
        }
        $mime = $this->guessMime($storagePath);
        try {
            $image = $this->loadImage($absolute, $mime);
        } catch (RuntimeException) {
            return null;
        }

        return $this->encodeBoundedJpeg($image, $maxWidth);
    }

    private function gdSupportsImages(): bool
    {
        return function_exists('imagecreatefromjpeg') && function_exists('imagejpeg');
    }

    private function guessMime(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }

    private function loadImage(string $path, string $mime): \GdImage
    {
        $image = match ($mime) {
            'image/jpeg', 'image/jpg' => @\imagecreatefromjpeg($path),
            'image/png' => @\imagecreatefrompng($path),
            'image/gif' => @\imagecreatefromgif($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @\imagecreatefromwebp($path) : false,
            default => false,
        };
        if (! $image instanceof \GdImage) {
            throw new RuntimeException('Unsupported image.');
        }

        return $image;
    }

    private function encodeBoundedJpeg(\GdImage $image, int $maxWidth): string
    {
        $width = \imagesx($image);
        $height = \imagesy($image);
        if ($width > $maxWidth) {
            $targetWidth = max(1, $maxWidth);
            $targetHeight = max(1, (int) round($height * ($maxWidth / max(1, $width))));
            $canvas = \imagecreatetruecolor($targetWidth, $targetHeight);
            if ($canvas === false) {
                \imagedestroy($image);
                throw new RuntimeException('Unable to allocate canvas.');
            }
            \imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
            \imagedestroy($image);
            $image = $canvas;
        }
        ob_start();
        \imagejpeg($image, null, self::JPEG_QUALITY);
        \imagedestroy($image);
        $binary = ob_get_clean();
        if ($binary === false || $binary === '') {
            throw new RuntimeException('Unable to encode image.');
        }

        return $binary;
    }
}
