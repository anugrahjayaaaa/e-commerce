<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    /**
     * Handle flexible image processing for uploads.
     *
     * @param UploadedFile $image
     * @param string $mainPath Directory for the primary image
     * @param bool $resizeMain Whether to resize the main image instead of moving it raw
     * @param array|null $mainDims Main dimensions ['w' => int, 'h' => int] if resized
     * @param string|null $thumbPath Optional directory for the thumbnail
     * @param array|null $thumbDims Optional thumbnail dimensions ['w' => int, 'h' => int]
     * @param string $oldImageName Optional handle old image deletion during updates
     * @return string
     */
    public function uploadAndProcessImage(
        UploadedFile $image,
        string $mainPath,
        bool $resizeMain = false,
        ?array $mainDims = null,
        ?string $thumbPath = null,
        ?array $thumbDims = null,
        ?string $oldImageName = null
    ): string {

        // 1. Delete old images if they exist (Clean-up for updates)
        if ($oldImageName) {
            // Remove from main path (ensure correct public formatting)
            $oldMainFile = str_contains($mainPath, 'public_path') ? $mainPath . '/' . $oldImageName : public_path(rtrim($mainPath, '/') . '/' . $oldImageName);
            if (file_exists($oldMainFile)) {
                @unlink($oldMainFile);
            }

            // Remove from thumbnail path if it exists
            if ($thumbPath) {
                $oldThumbFile = str_contains($thumbPath, 'public_path') ? $thumbPath . '/' . $oldImageName : public_path(rtrim($thumbPath, '/') . '/' . $oldImageName);
                if (file_exists($oldThumbFile)) {
                    @unlink($oldThumbFile);
                }
            }
        }

        // Generate a single unique file name used across all versions
        $imageName = time() . "_" . uniqid() . "." . $image->extension();

        // 1. Handle Thumbnail Image (If provided)
        if ($thumbPath && $thumbDims) {
            $this->resizeAndSaveImage($image, $imageName, $thumbPath, $thumbDims['w'], $thumbDims['h']);
        }

        // 2. Handle Main/Original Image
        if ($resizeMain && $mainDims) {
            // Case for Product: Resize the main image instead of moving the raw file
            $this->resizeAndSaveImage($image, $imageName, $mainPath, $mainDims['w'], $mainDims['h']);
        } else {
            // Case for Brand & Category: Move the original un-resized file
            $image->move($mainPath, $imageName);
        }

        // Return the name for database storage
        return $imageName;
    }

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
