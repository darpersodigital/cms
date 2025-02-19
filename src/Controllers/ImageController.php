<?php

namespace Darpersodigital\Cms\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Decoders\DataUriImageDecoder;
use Intervention\Image\Decoders\Base64ImageDecoder;
use Intervention\Image\Decoders\FilePathImageDecoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\AutoEncoder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ImageController extends BaseController
{

    public function compressAndUploadImage($file, $route) {
        $imgSize = $file ->getSize();
   
        $quality = 10;
        if($imgSize>=5101546) { 
            $quality = 3;
        }else if($imgSize>=2101546) { 
            $quality = 10;
        }else if($imgSize>=1101546) {
            $quality = 8;
        } else if($imgSize>=906791) {
            $quality = 9;
        }else if($imgSize>=506791) {
            $quality = 12;
        } else if($imgSize>=506791) {
            $quality = 15;
        }  else if($imgSize>=100727){
            $quality = 20;
        }else if($imgSize>=50727){
            $quality = 80;
        }else {
            $quality = 95;
        }

        $imageName = $route.'/'.Str::uuid() .'.webp';
        if(extension_loaded('imageick')) {
            $manager = new ImageManager(new Driver());
            $extension = $file->getClientOriginalExtension();
            $resize = $manager->read($file)->encode(new WebpEncoder(quality: $quality));
            Storage::disk('public')->put($imageName, (string) $resize->__toString());
            return $imageName;
        }else {
            return $this->compressAndUploadFile($file,$route);
        }
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
