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

    /**
     * Delete a single image and its corresponding thumbnail from storage.
     *
     * @param string $imageName The filename stored in the database
     * @param string $mainPath Directory of the main image
     * @param string|null $thumbPath Directory of the thumbnail
     * @return void
     */
    public function deleteSingleImage(string $imageName, string $mainPath, ?string $thumbPath = null): void
    {
        // Format and delete from the main directory
        $mainFilePath = str_contains($mainPath, 'public/') || str_contains($mainPath, 'uploads/') ? public_path(rtrim($mainPath, '/') . '/' . $imageName) : rtrim($mainPath, '/') . '/' . $imageName;
        if (file_exists($mainFilePath)) {
            @unlink($mainFilePath);
        }

        // Format and delete from the thumbnail directory if provided
        if ($thumbPath) {
            $thumbFilePath = str_contains($thumbPath, 'public/') || str_contains($thumbPath, 'uploads/') ? public_path(rtrim($thumbPath, '/') . '/' . $imageName) : rtrim($thumbPath, '/') . '/' . $imageName;
            if (file_exists($thumbFilePath)) {
                @unlink($thumbFilePath);
            }
        }
    }

    /**
     * Handle multiple image uploads for a product gallery.
     *
     * @param array $files Array of UploadedFile objects
     * @param string $mainPath Directory for main images
     * @param array $mainDims Main dimensions ['w' => int, 'h' => int]
     * @param string $thumbPath Directory for thumbnails
     * @param array $thumbDims Thumbnail dimensions ['w' => int, 'h' => int]
     * @param array $currentGallery Existing image names array to append to
     * @return array Updated gallery array containing filenames
     */
    public function processGalleryImages(
        array $files,
        string $mainPath,
        array $mainDims,
        string $thumbPath,
        array $thumbDims,
        array $currentGallery = []
    ): array {
        $allowedExtensions = ['jpg', 'png', 'jpeg', 'webp'];
        $counter = count($currentGallery) + 1;

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();

            // Validate file extension
            if (in_array(strtolower($extension), $allowedExtensions)) {
                $imageName = time() . "_" . uniqid() . "-" . $counter . "." . $extension;

                // Process and save main gallery image
                $this->resizeAndSaveImage($file, $imageName, $mainPath, $mainDims['w'], $mainDims['h']);

                // Process and save gallery thumbnail
                $this->resizeAndSaveImage($file, $imageName, $thumbPath, $thumbDims['w'], $thumbDims['h']);

                $currentGallery[] = $imageName;
                $counter++;
            }
        }

        return $currentGallery;
    }

    /**
     * Handle physical deletion of gallery images from storage.
     *
     * @param array $imagesToDelete Array of image names to delete
     * @param string $mainPath Directory of main images
     * @param string $thumbPath Directory of thumbnails
     * @return void
     */
    public function deleteGalleryImages(array $imagesToDelete, string $mainPath, string $thumbPath): void
    {
        foreach ($imagesToDelete as $image) {
            $image = trim($image);

            // Format paths correctly ensuring they target the public folder
            $mainFilePath = str_contains($mainPath, 'public/') || str_contains($mainPath, 'uploads/') ? public_path(rtrim($mainPath, '/') . '/' . $image) : rtrim($mainPath, '/') . '/' . $image;
            $thumbFilePath = str_contains($thumbPath, 'public/') || str_contains($thumbPath, 'uploads/') ? public_path(rtrim($thumbPath, '/') . '/' . $image) : rtrim($thumbPath, '/') . '/' . $image;

            if (file_exists($mainFilePath)) {
                @unlink($mainFilePath);
            }
            if (file_exists($thumbFilePath)) {
                @unlink($thumbFilePath);
            }
        }
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
