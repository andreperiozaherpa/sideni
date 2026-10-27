<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;

class ImageCompressor
{
    /**
     * Target maximum size for a compressed image, in bytes (500 KB).
     */
    public const MAX_BYTES = 512000;

    /**
     * Longest edge the image is downscaled to before quality tuning.
     */
    private const MAX_DIMENSION = 2000;

    /**
     * Smallest edge we are willing to downscale to.
     */
    private const MIN_DIMENSION = 640;

    private const START_QUALITY = 85;

    private const MIN_QUALITY = 30;

    /**
     * Compress an uploaded image so it fits under the target size.
     *
     * Returns null when the file is not a supported image or when GD/its
     * encoders are unavailable, so the caller stores the original instead of
     * failing the upload.
     *
     * @return array{bytes: string, extension: string, mime: string}|null
     */
    public function compress(UploadedFile $file): ?array
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $mime = (string) ($file->getMimeType() ?: '');

        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        try {
            $contents = @file_get_contents($file->getRealPath());
            $image = $contents === false ? false : @imagecreatefromstring($contents);
        } catch (\Throwable) {
            return null;
        }

        if ($image === false) {
            return null;
        }

        $original = $image;
        $image = $this->downscale($image, self::MAX_DIMENSION);

        try {
            $result = $this->fitUnderLimit($image, $mime);
        } catch (\Throwable) {
            $result = null;
        }

        imagedestroy($image);
        if ($original !== $image) {
            imagedestroy($original);
        }

        return $result;
    }

    /**
     * @return array{bytes: string, extension: string, mime: string}
     */
    private function fitUnderLimit(GdImage $image, string $mime): array
    {
        $extension = $this->extensionFor($mime);
        $quality = self::START_QUALITY;
        $bytes = $this->encode($image, $mime, $quality);

        while (strlen($bytes) > self::MAX_BYTES) {
            if ($mime !== 'image/png' && $quality > self::MIN_QUALITY) {
                $quality -= 15;
                $bytes = $this->encode($image, $mime, $quality);

                continue;
            }

            if (imagesx($image) <= self::MIN_DIMENSION) {
                break;
            }

            $image = $this->downscale($image, (int) round(imagesx($image) * 0.75));
            $quality = self::START_QUALITY;
            $bytes = $this->encode($image, $mime, $quality);
        }

        if (strlen($bytes) > self::MAX_BYTES && $mime === 'image/png' && ! $this->hasTransparency($image)) {
            $mime = 'image/jpeg';
            $extension = 'jpg';
            $bytes = $this->encode($image, $mime, 75);
        }

        return ['bytes' => $bytes, 'extension' => $extension, 'mime' => $mime];
    }

    private function encode(GdImage $image, string $mime, int $quality): string
    {
        ob_start();

        match ($mime) {
            'image/png' => imagepng($image, null, 9),
            'image/webp' => imagewebp($image, null, $quality),
            default => imagejpeg($image, null, $quality),
        };

        return (string) ob_get_clean();
    }

    /**
     * Return a copy of the image whose longest edge does not exceed the limit.
     * The source image is never destroyed here; the caller owns it.
     */
    private function downscale(GdImage $image, int $maxDimension): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        if ($longest <= $maxDimension) {
            return $image;
        }

        $ratio = $maxDimension / $longest;
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if ($this->hasTransparency($image)) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, imagecolorallocatealpha($resized, 0, 0, 0, 127));
        }

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $resized;
    }

    private function hasTransparency(GdImage $image): bool
    {
        if (! imageistruecolor($image)) {
            return imagecolortransparent($image) >= 0;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $stepX = max(1, (int) floor($width / 100));
        $stepY = max(1, (int) floor($height / 100));

        for ($x = 0; $x < $width; $x += $stepX) {
            for ($y = 0; $y < $height; $y += $stepY) {
                if (((imagecolorat($image, $x, $y) & 0x7F000000) >> 24) > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    private function extensionFor(string $mime): string
    {
        return match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
    }
}
