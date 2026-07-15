<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Exception;

trait HandlesModelImages
{
    /**
     * Validate that the controller using this trait has the required properties.
     * This prevents silent bugs and makes debugging much easier.
     *
     * @throws Exception
     */
    private function validateImageServiceRequirements(): void
    {
        if (!property_exists($this, 'imageService') || !$this->imageService) {
            throw new Exception("Trait Error: The controller must define and inject a protected \$imageService.");
        }

        if (!property_exists($this, 'mainPath')) {
            throw new Exception("Trait Error: The controller must define a protected string \$mainPath.");
        }
    }

    /**
     * Handle single image upload or update for any model.
     *
     * @param Request $request
     * @param object $model
     * @param string $fieldName The database column name for the image
     * @param bool $resize Whether the main image should be resized
     * @return string|null
     */
    protected function handleSingleImageUpload(Request $request, object $model, string $fieldName = 'image', bool $resize = true): ?string
    {
        $this->validateImageServiceRequirements();

        if ($request->hasFile($fieldName)) {
            // Dynamically retrieve configuration from the controller, or use safe defaults
            $mainDims = property_exists($this, 'mainDimensions') ? $this->mainDimensions : ['w' => 500, 'h' => 500];
            $thumbPath = property_exists($this, 'thumbnailPath') ? $this->thumbnailPath : null;
            $thumbDims = property_exists($this, 'thumbDimensions') ? $this->thumbDimensions : ['w' => 250, 'h' => 250];

            return $this->imageService->uploadAndProcessImage(
                $request->file($fieldName),
                $this->mainPath,
                $resize,
                $mainDims,
                $thumbPath,
                $thumbDims,
                $model->$fieldName // Passes the old file name to trigger auto-deletion
            );
        }

        // Return the existing image name if no new file is uploaded
        return $model->$fieldName ?? null;
    }

    /**
     * Handle gallery images upload and item deletions for any model.
     *
     * @param Request $request
     * @param object $model
     * @param string $fieldName The database column name for the gallery
     * @return string|null
     */
    protected function handleGalleryImageUpload(Request $request, object $model, string $fieldName = 'images'): ?string
    {
        $this->validateImageServiceRequirements();

        $currentGallery = !empty($model->$fieldName) ? explode(',', $model->$fieldName) : [];
        $thumbPath = property_exists($this, 'thumbnailPath') ? $this->thumbnailPath : ''; // Fallback to empty string for safety

        // Step 1: Handle requested gallery image deletions
        if ($request->has('deleted_gallery_images')) {
            $deletedImages = (array) $request->input('deleted_gallery_images');

            // Only attempt to delete images that actually exist in the current gallery array
            $validDeletions = array_intersect($deletedImages, $currentGallery);

            if (!empty($validDeletions)) {
                $this->imageService->deleteGalleryImages($validDeletions, $this->mainPath, $thumbPath);

                // Remove the deleted images from our tracking array
                $currentGallery = array_diff($currentGallery, $validDeletions);
            }
        }

        // Step 2: Handle new gallery image uploads
        if ($request->hasFile($fieldName)) {
            $galleryFiles = $request->file($fieldName);

            $galleryDims = property_exists($this, 'galleryDimensions') ? $this->galleryDimensions : ['w' => 500, 'h' => 500];
            $thumbDims = property_exists($this, 'thumbDimensions') ? $this->thumbDimensions : ['w' => 250, 'h' => 250];

            // Process and append new images to the existing gallery array
            $currentGallery = $this->imageService->processGalleryImages(
                $galleryFiles,
                $this->mainPath,
                $galleryDims,
                $thumbPath,
                $thumbDims,
                $currentGallery
            );
        }

        // Re-index array keys cleanly and convert to comma-separated string
        $currentGallery = array_values($currentGallery);
        return !empty($currentGallery) ? implode(',', $currentGallery) : null;
    }

    /**
     * Delete only the main single image of the model.
     *
     * @param object $model
     * @param string $fieldName
     * @return void
     */
    protected function deleteSingleModelImage(object $model, string $fieldName = 'image'): void
    {
        $this->validateImageServiceRequirements();

        $thumbPath = property_exists($this, 'thumbnailPath') ? $this->thumbnailPath : null;

        if (!empty($model->$fieldName)) {
            $this->imageService->deleteSingleImage($model->$fieldName, $this->mainPath, $thumbPath);
        }
    }

    /**
     * Delete all images (Main + Gallery) associated with the given model.
     *
     * @param object $model
     * @param string $singleField
     * @param string $galleryField
     * @return void
     */
    protected function deleteModelImages(object $model, string $singleField = 'image', string $galleryField = 'images'): void
    {
        $this->validateImageServiceRequirements();

        $thumbPath = property_exists($this, 'thumbnailPath') ? $this->thumbnailPath : '';

        // 1. Remove the main single image
        $this->deleteSingleModelImage($model, $singleField);

        // 2. Remove the gallery images
        if (!empty($model->$galleryField)) {
            $galleryImages = explode(',', $model->$galleryField);
            $this->imageService->deleteGalleryImages($galleryImages, $this->mainPath, $thumbPath);
        }
    }
}
