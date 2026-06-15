<?php

namespace App\Support\Media;

use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Вспомогательный класс image processor.
 */
final class ImageProcessor
{
    private const AVATAR_SIZE = 512;

    private const FEATURED_MAX_WIDTH = 1920;

    private const CONTENT_MAX_WIDTH = 1600;

    private const BACKGROUND_MAX_WIDTH = 2560;

    private const JPEG_QUALITY = 85;

    /**
     * Resize and compress an avatar to a square JPEG blob.

     *
     * @return string
     */
    public function processAvatar(UploadedFile $file): string
    {
        if (! $this->gdSupportsImages()) {
            $contents = is_string($file->getRealPath()) && is_readable($file->getRealPath()) ? file_get_contents($file
                ->getRealPath()) : $file->get();
            if (! is_string($contents) || $contents === '') {
                throw new RuntimeException('Unable to read uploaded image.');
            }

            return $contents;
        }
        $path = $file->getRealPath();
        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Unable to read uploaded image.');
        }
        $image = $this->loadImage($path, $file->getMimeType() ?? '');

        return $this->encodeSquareJpeg($image);
    }

    /**
     * process featured image.

     *
     * @return string
     */
    public function processFeaturedImage(UploadedFile $file): string
    {
        return $this->processBoundedJpeg($file, self::FEATURED_MAX_WIDTH);
    }

    /**
     * process content image.

     *
     * @return string
     */
    public function processContentImage(UploadedFile $file): string
    {
        return $this->processBoundedJpeg($file, self::CONTENT_MAX_WIDTH);
    }

    /**
     * process background image.

     *
     * @return string
     */
    public function processBackgroundImage(UploadedFile $file): string
    {
        return $this->processBoundedJpeg($file, self::BACKGROUND_MAX_WIDTH);
    }

    private function processBoundedJpeg(UploadedFile $file, int $maxWidth): string
    {
        if (! $this->gdSupportsImages()) {
            $contents = is_string($file->getRealPath()) && is_readable($file->getRealPath()) ? file_get_contents($file
                ->getRealPath()) : $file->get();
            if (! is_string($contents) || $contents === '') {
                throw new RuntimeException('Unable to read uploaded image.');
            }

            return $contents;
        }
        $path = $file->getRealPath();
        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Unable to read uploaded image.');
        }
        $image = $this->loadImage($path, $file->getMimeType() ?? '');

        return $this->encodeBoundedJpeg($image, $maxWidth);
    }

    private function gdSupportsImages(): bool
    {
        return function_exists('imagecreatefromjpeg') && function_exists('imagejpeg');
    }

    private function encodeSquareJpeg(\GdImage $image): string
    {
        $width = \imagesx($image);
        $height = \imagesy($image);
        $size = min($width, $height);
        $offsetX = (int) max(0, ($width - $size) / 2);
        $offsetY = (int) max(0, ($height - $size) / 2);
        $canvas = \imagecreatetruecolor(self::AVATAR_SIZE, self::AVATAR_SIZE);
        if ($canvas === false) {
            \imagedestroy($image);
            throw new RuntimeException('Unable to allocate avatar canvas.');
        }
        \imagecopyresampled($canvas, $image, 0, 0, $offsetX, $offsetY, self::AVATAR_SIZE, self::AVATAR_SIZE, $size,
            $size);
        \imagedestroy($image);
        ob_start();
        \imagejpeg($canvas, null, self::JPEG_QUALITY);
        \imagedestroy($canvas);
        $binary = ob_get_clean();
        if ($binary === false || $binary === '') {
            throw new RuntimeException('Unable to encode avatar image.');
        }

        return $binary;
    }

    private function encodeBoundedJpeg(\GdImage $image, int $maxWidth): string
    {
        $width = \imagesx($image);
        $height = \imagesy($image);
        if ($width <= $maxWidth) {
            ob_start();
            \imagejpeg($image, null, self::JPEG_QUALITY);
            \imagedestroy($image);
            $binary = ob_get_clean();
            if ($binary === false || $binary === '') {
                throw new RuntimeException('Unable to encode image.');
            }

            return $binary;
        }
        $targetWidth = max(1, $maxWidth);
        $targetHeight = max(1, (int) round($height * ($maxWidth / max(1, $width))));
        $canvas = \imagecreatetruecolor($targetWidth, $targetHeight);
        if ($canvas === false) {
            \imagedestroy($image);
            throw new RuntimeException('Unable to allocate image canvas.');
        }
        \imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        \imagedestroy($image);
        ob_start();
        \imagejpeg($canvas, null, self::JPEG_QUALITY);
        \imagedestroy($canvas);
        $binary = ob_get_clean();
        if ($binary === false || $binary === '') {
            throw new RuntimeException('Unable to encode image.');
        }

        return $binary;
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
            throw new RuntimeException('Unsupported or corrupted image file.');
        }

        return $image;
    }
}
