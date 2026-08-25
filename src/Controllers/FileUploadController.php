<?php

namespace Darpersodigital\Cms\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Intervention\Image\ImageManager;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends BaseController
{
    /**
     * The quality WebP re-encodes at when the source is small enough that the ladder
     * below has nothing to claw back. Kept high enough to keep document scans and
     * fine print legible. Override per project with IMAGE_COMPRESSION_QUALITY.
     */
    private const DEFAULT_IMAGE_QUALITY = 82;

    public function bytesToMegabytes($bytes, $binary = false, $precision = 2)
    {
        if ($binary) {
            return round($bytes / 1048576, $precision);
        } else {
            return round($bytes / 1000000, $precision);
        }
    }

    public function compressAndUploadImage($file, $route)
    {
        if (!$this->isCompressibleImage($file)) {
            return $this->compressAndUploadFile($file, $route);
        }

        $imageName = $route . '/' . Str::uuid() . '.webp';

        try {
            // GD holds the decoded bitmap uncompressed: a 4032x3024 phone photo is ~48MB
            // and an 8000px document scan ~190MB. Document fields now reach this method
            // too (see handleSingleFileUpload), so the ceiling has to cover scans as well
            // as photos.
            ini_set('memory_limit', '512M');

            $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->read($file);

            // A phone records its rotation in an EXIF tag rather than in the pixels, and
            // that tag does not survive the re-encode below - so the rotation has to be
            // baked into the pixels first, or portrait photos come back on their side.
            $image->orient();

            $webpData = (string) $image->toWebp(quality: $this->imageQuality($file));
            Storage::disk('public')->put($imageName, $webpData);
        } catch (\Throwable $e) {
            // Decoding can still fail on a corrupt file or one too large for the limit
            // above. Storing the original beats losing the upload entirely.
            return $this->compressAndUploadFile($file, $route);
        }

        return $imageName;
    }

    /**
     * Whether the upload is a raster image we can re-encode. SVG is an image by mime
     * but a document to the decoder, and rasterising it would destroy it.
     */
    private function isCompressibleImage($file): bool
    {
        $mime = $file?->getMimeType();

        return is_string($mime) && str_starts_with($mime, 'image/') && $mime !== 'image/svg+xml';
    }

    private function imageQuality($file): int
    {
        if (filter_var(env('DISABLE_IMAGE_COMPRESSION', false), FILTER_VALIDATE_BOOLEAN)) {
            // Quality 100 re-encodes at max fidelity but often inflates file size well beyond
            // the source (a re-encode still fully re-compresses the image); 95 is visually
            // lossless while staying close to source size.
            return 95;
        }

        $configured = env('IMAGE_COMPRESSION_QUALITY');

        if ($configured !== null && $configured !== '' && is_numeric($configured)) {
            // An explicit quality in .env overrides the size-based ladder below.
            return max(1, min(100, (int) $configured));
        }

        // Bigger sources still compress harder, since dimensions are left alone and
        // quality is the only lever - but the ladder is floored well above the point
        // where a document scan stops being legible.
        $imgSize = +$this->bytesToMegabytes($file->getSize());

        if ($imgSize >= 5) {
            return 60;
        }

        if ($imgSize >= 3) {
            return 68;
        }

        if ($imgSize >= 1) {
            return 75;
        }

        return self::DEFAULT_IMAGE_QUALITY;
    }
    public function normalizeMultipleFilesArray($value)
    {
        // Step 1: Decode repeatedly if string
        while (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                break;
            }

            $value = $decoded;
        }

        // Step 2: Ensure array
        if (!is_array($value)) {
            $value = [$value];
        }

        $result = [];

        foreach ($value as $item) {
            // Step 3: Decode nested JSON strings like "[]", "[null]", "[\"file.webp\"]"
            if (is_string($item)) {
                $decoded = json_decode($item, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    if (is_array($decoded)) {
                        foreach ($decoded as $subItem) {
                            if ($this->isValidFileValue($subItem)) {
                                $result[] = $subItem;
                            }
                        }
                        continue;
                    }

                    $item = $decoded;
                }
            }

            // Step 4: Filter valid values
            if ($this->isValidFileValue($item)) {
                $result[] = $item;
            }
        }

        return array_values($result);
    }
    private function isValidFileValue($value): bool
    {
        return !is_null($value) && $value !== '' && $value !== '[]' && $value !== '[null]';
    }
    public function compressAndUploadFile($file, $route)
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;

        return $file->storeAs($route, $fileName, 'public');
    }

    /**
     * Route on what the file actually is rather than on what the form called the
     * field: a 5MB phone photo arrives on a document field as often as on a photo
     * one, and the field name alone let those through untouched. Everything that
     * isn't a raster image - PDFs, video, SVG - is still stored byte for byte.
     *
     * $form_field is kept for callers that still pass it, but no longer decides.
     */
    public function handleSingleFileUpload($file, $route, $form_field)
    {
        if ($this->isCompressibleImage($file)) {
            return $this->compressAndUploadImage($file, $route);
        }

        return $this->compressAndUploadFile($file, $route);
    }

    public function handleMultipleFilesUpload($request, $field_name, $route, $form_field, $with_alt = false, $locale = null)
    {
        $files = [];
        if ($locale) {
            if ($request->hasFile($locale . '.' . $field_name)) {
                foreach ($request->file($locale . '.' . $field_name) as $file) {
                    if ($with_alt) {
                        $file_path = [
                            'file' => $this->handleSingleFileUpload($file, $route, $form_field),
                            'alt' => '',
                        ];
                    } else {
                        $file_path = $this->handleSingleFileUpload($file, $route, $form_field);
                    }

                    $files[] = $file_path;
                }
            }
        } else {
            if ($request->hasFile($field_name)) {
                foreach ($request->file($field_name) as $index => $file) {
                    if ($with_alt) {
                        $file_path = [
                            'file' => $this->handleSingleFileUpload($file, $route, $form_field),
                            'alt' => '',
                        ];
                    } else {
                        $file_path = $this->handleSingleFileUpload($file, $route, $form_field);
                    }

                    $files[] = $file_path;
                }
            }
        }
        return $files;
    }
}
