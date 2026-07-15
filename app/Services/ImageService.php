<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image;
use Exception;

class ImageService
{
    /**
     * Handle flexible image processing for uploads.
     *
     * @param UploadedFile $image
     * @param string $mainPath Directory for the primary image (relative to public path)
     * @param bool $resizeMain Whether to resize the main image instead of moving it raw
     * @param array|null $mainDims Main dimensions ['w' => int, 'h' => int] if resized
     * @param string|null $thumbPath Optional directory for the thumbnail
     * @param array|null $thumbDims Optional thumbnail dimensions ['w' => int, 'h' => int]
     * @param string|null $oldImageName Optional handle old image deletion during updates
     * @return string
     * @throws Exception
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
        try {
            // 1. Delete old images if they exist (Clean-up for updates)
            if ($oldImageName) {
                $this->deleteSingleImage($oldImageName, $mainPath, $thumbPath);
            }

            // Generate a single unique file name used across all versions
            $imageName = time() . "_" . uniqid() . "." . $image->extension();

            // 2. Handle Thumbnail Image (If provided)
            if (!empty($thumbPath) && !empty($thumbDims)) {
                $this->resizeAndSaveImage($image, $imageName, $thumbPath, $thumbDims['w'], $thumbDims['h']);
            }

            // 3. Handle Main/Original Image
            if ($resizeMain && !empty($mainDims)) {
                // Resize the main image
                $this->resizeAndSaveImage($image, $imageName, $mainPath, $mainDims['w'], $mainDims['h']);
            } else {
                // Move the original un-resized file
                $absoluteMainPath = public_path(trim($mainPath, '/'));
                $this->ensureDirectoryExists($absoluteMainPath);
                $image->move($absoluteMainPath, $imageName);
            }

            return $imageName;
        } catch (Exception $e) {
            // Log the error for easy debugging without breaking the application silently
            Log::error("Image Upload Failed: " . $e->getMessage());
            throw new Exception("Failed to process image upload. Please check the logs.");
        }
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
        $this->deleteFileSafely($mainPath, $imageName);

        if (!empty($thumbPath)) {
            $this->deleteFileSafely($thumbPath, $imageName);
        }
    }

    /**
     * Handle multiple image uploads for a product gallery.
     *
     * @param array $files Array of UploadedFile objects
     * @param string $mainPath Directory for main images
     * @param array $mainDims Main dimensions ['w' => int, 'h' => int]
     * @param string|null $thumbPath Directory for thumbnails
     * @param array|null $thumbDims Thumbnail dimensions ['w' => int, 'h' => int]
     * @param array $currentGallery Existing image names array to append to
     * @return array Updated gallery array containing filenames
     */
    public function processGalleryImages(
        array $files,
        string $mainPath,
        array $mainDims,
        ?string $thumbPath = null,
        ?array $thumbDims = null,
        array $currentGallery = []
    ): array {
        $counter = count($currentGallery) + 1;

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue; // Skip if somehow array contains invalid file data
            }

            $imageName = time() . "_" . uniqid() . "-" . $counter . "." . $file->extension();

            // Process and save main gallery image
            $this->resizeAndSaveImage($file, $imageName, $mainPath, $mainDims['w'], $mainDims['h']);

            // Process and save gallery thumbnail if dimensions are provided
            if (!empty($thumbPath) && !empty($thumbDims)) {
                $this->resizeAndSaveImage($file, $imageName, $thumbPath, $thumbDims['w'], $thumbDims['h']);
            }

            $currentGallery[] = $imageName;
            $counter++;
        }

        return $currentGallery;
    }

    /**
     * Handle physical deletion of gallery images from storage.
     *
     * @param array $imagesToDelete Array of image names to delete
     * @param string $mainPath Directory of main images
     * @param string|null $thumbPath Directory of thumbnails
     * @return void
     */
    public function deleteGalleryImages(array $imagesToDelete, string $mainPath, ?string $thumbPath = null): void
    {
        foreach ($imagesToDelete as $image) {
            $image = trim($image);
            $this->deleteSingleImage($image, $mainPath, $thumbPath);
        }
    }

    /**
     * Helper: Resize and save an image using Intervention.
     */
    private function resizeAndSaveImage(UploadedFile $image, string $imageName, string $folder, int $width, int $height): void
    {
        $savePath = public_path(trim($folder, '/'));

        $this->ensureDirectoryExists($savePath);

        Image::decode($image)
            ->resize($width, $height)
            ->save($savePath . '/' . $imageName);
    }

    /**
     * Helper: Safely resolve path and delete a file if it exists.
     */
    private function deleteFileSafely(string $folder, string $fileName): void
    {
        // Standardize the path format cleanly
        $absolutePath = public_path(trim($folder, '/') . '/' . trim($fileName));

        if (File::exists($absolutePath) && File::isFile($absolutePath)) {
            File::delete($absolutePath);
        }
    }

    /**
     * Helper: Safely create directory if it does not exist.
     */
    private function ensureDirectoryExists(string $path): void
    {
        if (!File::isDirectory($path)) {
            // Creates directory recursively with 0755 permissions
            File::makeDirectory($path, 0755, true, true);
        }
    }
}
