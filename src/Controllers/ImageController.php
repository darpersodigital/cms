<?php

namespace Darpersodigital\Cms\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Intervention\Image\ImageManager;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ImageController extends BaseController
{

    public function bytesToMegabytes($bytes, $binary = false, $precision = 2) {
    if ($binary) {
        return round($bytes / 1048576, $precision) ;
    } else {
        return round($bytes / 1000000, $precision) ;
    }
}

    public function compressAndUploadImage($file, $route) {
        
        if ($file->getMimeType() === 'image/svg+xml') {
          return $this->compressAndUploadFile($file,$route);
        }
        
        $imgSize = +$this->bytesToMegabytes($file->getSize());

        if($imgSize>= 10){
            $quality = 1;
        }else if($imgSize>= 9){
            $quality = 4;
        }else  if($imgSize>=8) { 
            $quality = 6;
        }else if($imgSize>=7) { 
            $quality = 7;
        }else if($imgSize>=5) {
            $quality = 20;
        } else if($imgSize>=4) {
            $quality = 25;
        }else if($imgSize>=3) {
            $quality = 30;
        } else if($imgSize>=2) {
            $quality = 50;
        }  else if($imgSize>=1){
            $quality = 65;
        }else if($imgSize>=0.7){
            $quality = 75;
        } else if($imgSize>=0.6){
            $quality = 80;
        } else if($imgSize>=0.5){
            $quality = 85;
        } else if($imgSize>=0.25){
            $quality = 90;
        }else {
            
        }
        $quality = 40;
        $imageName = $route.'/'.Str::uuid() .'.webp';

        try {
            // ini_set('memory_limit', '512M');
            $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver);
            $image = $manager->read($file);
            $webpData = (string) $image->toWebp(quality: $quality);
            Storage::disk('public')->put($imageName, $webpData);
        } catch (Exception $e){
            return $this->compressAndUploadFile($file,$route);
        }
     
        return $imageName;
    }


    public function compressAndUploadFile($file, $route) {
        $extension = $file->getClientOriginalExtension();
        $imageName = $route.'/'.Str::uuid() .'.' .$extension;
        $path = $file->store($route,'public');
        return $path;
    }


    public function compressAndUploadMultipleImages($request,$field_name, $route,$locale='') {
        $files = [];
        if($locale!=='') {
            if ($request->hasFile($locale.'.'.$field_name)) {
                foreach ($request->file($locale.'.'.$field_name) as $file) {
                    $file_path =  $this->compressAndUploadImage($file, $route);
                    $files[] = $file_path;
                }
            }
        }else {
            if ($request->hasFile($field_name)) {
                foreach ($request->file($field_name) as $file) {
                    $file_path =  $this->compressAndUploadImage($file, $route);
                    $files[] = $file_path;
                }
            }
        }
        return $files;
    }

}
