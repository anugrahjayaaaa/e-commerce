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
}
