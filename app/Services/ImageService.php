<?php

namespace App\Services;

use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    public function resizeAndSaveImage($image, $imageName, $folder, $width, $height)
    {
        $savePath = public_path($folder);

        // Create the directory if it does not exist
        if (!file_exists($savePath)) {
            mkdir($savePath, 0755, true);
        }

        // Process, resize, and save the image
        Image::decode($image)
            ->resize($width, $height)
            ->save($savePath . "/" . $imageName);
    }
}
