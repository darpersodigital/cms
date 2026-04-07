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
        $mime = $file->getMimeType();
        if (!str_starts_with($mime, 'image/') || $mime === 'image/svg+xml') {
            return $this->compressAndUploadFile($file, $route);
        }

        $imgSize = +$this->bytesToMegabytes($file->getSize());

        if ($imgSize >= 10) {
            $quality = 1;
        } elseif ($imgSize >= 9) {
            $quality = 4;
        } elseif ($imgSize >= 8) {
            $quality = 6;
        } elseif ($imgSize >= 7) {
            $quality = 7;
        } elseif ($imgSize >= 5) {
            $quality = 20;
        } elseif ($imgSize >= 4) {
            $quality = 25;
        } elseif ($imgSize >= 3) {
            $quality = 30;
        } elseif ($imgSize >= 2) {
            $quality = 50;
        } elseif ($imgSize >= 1) {
            $quality = 65;
        } elseif ($imgSize >= 0.7) {
            $quality = 75;
        } elseif ($imgSize >= 0.6) {
            $quality = 80;
        } elseif ($imgSize >= 0.5) {
            $quality = 85;
        } elseif ($imgSize >= 0.25) {
            $quality = 90;
        } else {
        }
        $quality = 40;
        $imageName = $route . '/' . Str::uuid() . '.webp';

        // try {
        // ini_set('memory_limit', '512M');
        $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $image = $manager->read($file);
        $webpData = (string) $image->toWebp(quality: $quality);
        Storage::disk('public')->put($imageName, $webpData);
        // } catch (Exception $e){
        //     return $this->compressAndUploadFile($file,$route);
        // }

        return $imageName;
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

    public function handleSingleFileUpload($file, $route, $form_field)
    {
        if (str_contains($form_field, 'image')) {
            return $this->compressAndUploadImage($file, $route);
        } elseif (str_contains($form_field, 'video')) {
            return $this->compressAndUploadFile($file, $route);
        } else {
            return $this->compressAndUploadFile($file, $route);
        }
    }

    public function handleMultipleFilesUpload($request, $field_name, $route, $form_field, $locale = null)
    {
        $files = [];
        if ($locale) {
            if ($request->hasFile($locale . '.' . $field_name)) {
                foreach ($request->file($locale . '.' . $field_name) as $file) {
                    $file_path = $this->handleSingleFileUpload($file, $route,$form_field);
                    $files[] = $file_path;
                }
            }
        } else {
            if ($request->hasFile($field_name)) {
                foreach ($request->file($field_name) as $file) {
                    $file_path = $this->handleSingleFileUpload($file, $route,$form_field);
                    $files[] = $file_path;
                }
            }
        }
        return $files;
    }

    // public function compressAndUploadMultipleImages($request, $field_name, $route, $locale = null)
    // {
    //     $files = [];
    //     if ($locale) {
    //         if ($request->hasFile($locale . '.' . $field_name)) {
    //             foreach ($request->file($locale . '.' . $field_name) as $file) {
    //                 $file_path = $this->compressAndUploadImage($file, $route);
    //                 $files[] = $file_path;
    //             }
    //         }
    //     } else {
    //         if ($request->hasFile($field_name)) {
    //             foreach ($request->file($field_name) as $file) {
    //                 $file_path = $this->compressAndUploadImage($file, $route);
    //                 $files[] = $file_path;
    //             }
    //         }
    //     }
    //     return $files;
    // }

    // public function compressAndUploadMultipleFiles($request, $field_name, $route, $locale = null)
    // {
    //     $files = [];
    //     if ($locale) {
    //         if ($request->hasFile($locale . '.' . $field_name)) {
    //             foreach ($request->file($locale . '.' . $field_name) as $file) {
    //                 $file_path = $this->compressAndUploadFile($file, $route);
    //                 $files[] = $file_path;
    //             }
    //         }
    //     } else {
    //         if ($request->hasFile($field_name)) {
    //             foreach ($request->file($field_name) as $file) {
    //                 $file_path = $this->compressAndUploadFile($file, $route);
    //                 $files[] = $file_path;
    //             }
    //         }
    //     }
    //     return $files;
    // }
}
