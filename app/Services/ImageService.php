<?php

namespace App\Services;

use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    public function generateThumbnailImage($image, $imageName, $folder, $witdh = 124, $height = 124)
    {
        $thumnailPath = public_path($folder . "/thumbnails");
        if (!file_exists($thumnailPath)) {
            mkdir($thumnailPath, 0755, true);
        }

        // resize image
        Image::decode($image)
            ->resize($witdh, $height)
            ->save($thumnailPath . "/" . $imageName);
    }

    public function resizeAndSaveImage($image, $imageName, $folder, $witdh = 270, $height = 303)
    {
        $imagePath = public_path($folder);
        if (!file_exists($imagePath)) {
            mkdir($imagePath, 0755, true);
        }

        // resize image
        Image::decode($image)
            ->resize($witdh, $height)
            ->save($imagePath . "/" . $imageName);
    }
}
