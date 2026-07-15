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
     * Handles the upload and deletion logic for gallery images.
     * 
     * @param Request $request - The incoming HTTP request.
     * @param object $model - The Eloquent model being updated.
     * @param string $fieldName - The database column name storing the images.
     * @returns string|null A comma-separated string of valid filenames, or null if empty.
     */
    protected function handleGalleryImageUpload(Request $request, object $model, string $fieldName = 'images'): ?string
    {
        $this->validateImageServiceRequirements();

        // 1. Safe Initialization: Ensures compatibility regardless of whether the model casts the field as an array or a string.
        $fieldValue = $model->$fieldName;
        $currentGallery = is_array($fieldValue) ? $fieldValue : (!empty($fieldValue) ? explode(',', $fieldValue) : []);

        $thumbPath = property_exists($this, 'thumbnailPath') ? $this->thumbnailPath : '';

        // ---------------------------------------------------------
        // 2. Handle Gallery Deletions
        // ---------------------------------------------------------
        if ($request->has('deleted_gallery_images')) {
            $deletedImages = (array) $request->input('deleted_gallery_images');

            // Intersection ensures we only delete images that actually belong to this model, preventing arbitrary file deletion attacks.
            $validDeletions = array_intersect($deletedImages, $currentGallery);

            if (!empty($validDeletions)) {
                $this->imageService->deleteGalleryImages($validDeletions, $this->mainPath, $thumbPath);
                $currentGallery = array_diff($currentGallery, $validDeletions);
            }
        }

        // ---------------------------------------------------------
        // 3. Handle New Uploads
        // ---------------------------------------------------------
        if ($request->hasFile($fieldName)) {
            $galleryFiles = $request->file($fieldName);

            // Defensive check: Ensure we actually received an array of files before processing.
            if (is_array($galleryFiles)) {
                $galleryDims = property_exists($this, 'galleryDimensions') ? $this->galleryDimensions : ['w' => 500, 'h' => 500];
                $thumbDims = property_exists($this, 'thumbDimensions') ? $this->thumbDimensions : ['w' => 250, 'h' => 250];

                $currentGallery = $this->imageService->processGalleryImages(
                    $galleryFiles,
                    $this->mainPath,
                    $galleryDims,
                    $thumbPath,
                    $thumbDims,
                    $currentGallery
                );
            }
        }

        // ---------------------------------------------------------
        // 4. Data Sanitization
        // ---------------------------------------------------------

        // HACK: Strip out any raw UploadedFile objects or temporary server paths that might have leaked from the ImageService.
        // This prevents database corruption where temporary paths (e.g., /private/var/tmp) are saved instead of actual filenames.
        $sanitizedGallery = array_filter($currentGallery, function ($item) {
            return is_string($item)
                && !str_starts_with($item, '/private/var/tmp')
                && !str_starts_with($item, '/tmp');
        });

        // Re-index array keys cleanly to avoid index gaps before imploding to a string.
        $sanitizedGallery = array_values($sanitizedGallery);

        return !empty($sanitizedGallery) ? implode(',', $sanitizedGallery) : null;
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
