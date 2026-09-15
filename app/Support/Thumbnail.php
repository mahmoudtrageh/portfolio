<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Downscales an uploaded raster image in place.
 *
 * A favicon is drawn at 16–32px in a browser tab, so shipping the original
 * upload means downloading a full-size photograph on every page load to render
 * a thumbnail. This shrinks it once, at upload time, rather than asking the
 * visitor to pay for it on every request.
 *
 * Failure is never fatal: if the image cannot be processed the original file
 * stays exactly as uploaded, which is worse for bandwidth but still correct.
 */
final class Thumbnail
{
    /**
     * Resize the stored image so neither side exceeds $max pixels.
     *
     * SVG is vector and already small, so it is left alone.
     */
    public static function shrink(string $path, int $max = 512): void
    {
        $disk = Storage::disk('public');

        if ($path === '' || ! $disk->exists($path)) {
            return;
        }

        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'svg') {
            return;
        }

        try {
            $source = @imagecreatefromstring($disk->get($path));

            if ($source === false) {
                return;
            }

            $width = imagesx($source);
            $height = imagesy($source);
            $longest = max($width, $height);

            // Already small enough — re-encoding would only lose quality.
            if ($longest <= $max) {
                imagedestroy($source);

                return;
            }

            $scale = $max / $longest;

            // A very lopsided image can round its short side to zero, which
            // imagecreatetruecolor rejects — so never go below one pixel.
            $targetWidth = max(1, (int) round($width * $scale));
            $targetHeight = max(1, (int) round($height * $scale));

            $target = imagecreatetruecolor($targetWidth, $targetHeight);

            // Preserve transparency, which a logo or icon almost always has.
            imagealphablending($target, false);
            imagesavealpha($target, true);

            imagecopyresampled(
                $target, $source,
                0, 0, 0, 0,
                $targetWidth, $targetHeight,
                $width, $height,
            );

            ob_start();
            imagepng($target, null, 9);
            $encoded = (string) ob_get_clean();

            imagedestroy($source);
            imagedestroy($target);

            if ($encoded !== '') {
                $disk->put($path, $encoded);
            }
        } catch (Throwable) {
            // Keep the original rather than losing the upload.
        }
    }
}
