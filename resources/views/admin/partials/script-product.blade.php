<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ==========================================
        // 1. AUTO-SLUG GENERATOR
        // ==========================================
        const productNameInput = document.getElementById('name');
        const productSlugInput = document.getElementById('slug');

        if (productNameInput && productSlugInput) {
            productNameInput.addEventListener('input', function() {
                const slug = this.value.toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9 -]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
                productSlugInput.value = slug;
            });
        }

        // ==========================================
        // 2. SINGLE NEW IMAGE UPLOAD (MAIN)
        // ==========================================
        const singleImageInput = document.getElementById('product-image');
        const singleUploadLabel = document.getElementById('single-upload-label');

        const singlePreviewContainer = document.getElementById('single-preview-container');
        const singleImagePreview = document.getElementById('single-image-preview');
        const removeSingleNewBtn = document.getElementById('remove-single-image');

        if (singleImageInput) {
            singleImageInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    singleImagePreview.src = URL.createObjectURL(file);
                    singleUploadLabel.classList.add('hidden');
                    singlePreviewContainer.classList.remove('hidden');
                    singlePreviewContainer.classList.add('flex');
                } else {
                    resetSingleNewImage();
                }
            });
        }

        if (removeSingleNewBtn) {
            removeSingleNewBtn.addEventListener('click', function(e) {
                e.preventDefault();
                resetSingleNewImage();
            });
        }

        function resetSingleNewImage() {
            singleImageInput.value = '';
            singleImagePreview.src = '';
            singlePreviewContainer.classList.add('hidden');
            singlePreviewContainer.classList.remove('flex');
            singleUploadLabel.classList.remove('hidden');
        }

        // ==========================================
        // 3. MULTIPLE NEW IMAGE UPLOAD (GALLERY)
        // ==========================================
        const galleryInput = document.getElementById('product-images');

        const galleryPreviewContainer = document.getElementById('gallery-preview-container');
        let selectedGalleryFiles = []; // Array to store multiple files

        if (galleryInput && galleryPreviewContainer) {
            galleryInput.addEventListener('change', function(event) {
                const files = Array.from(event.target.files);

                files.forEach(file => {
                    if (file.type.startsWith('image/')) {
                        // Prevent duplicates
                        const isDuplicate = selectedGalleryFiles.some(f => f.name === file
                            .name && f.size === file.size);
                        if (!isDuplicate) {
                            selectedGalleryFiles.push(file);
                        }
                    }
                });
                updateGalleryPreviews();
            });
        }

        function updateGalleryPreviews() {
            // Sync the HTML input
            const dataTransfer = new DataTransfer();
            selectedGalleryFiles.forEach(file => dataTransfer.items.add(file));
            galleryInput.files = dataTransfer.files;

            // Clear current previews
            galleryPreviewContainer.innerHTML = '';

            // Re-render
            selectedGalleryFiles.forEach((file, index) => {
                const objectUrl = URL.createObjectURL(file);

                const div = document.createElement('div');
                div.className =
                    'relative h-20 bg-gray-50 rounded border border-blue-200 flex items-center justify-center overflow-hidden group shadow-sm';

                const img = document.createElement('img');
                img.src = objectUrl;
                img.className = 'w-full h-full object-cover';

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className =
                    'absolute top-1 right-1 bg-red-500 text-white rounded-md w-6 h-6 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity focus:outline-none shadow-md';
                removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';

                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    selectedGalleryFiles.splice(index, 1);
                    updateGalleryPreviews();
                    URL.revokeObjectURL(objectUrl);
                });

                div.appendChild(img);
                div.appendChild(removeBtn);
                galleryPreviewContainer.appendChild(div);
            });
        }

        // ONLY IN EDIT/UPDATE PAGE
        // ==========================================
        // 4. REMOVE EXISTING IMAGES (TRACK FOR BACKEND)
        // ==========================================
        const removeExistingBtns = document.querySelectorAll('.remove-existing-btn');
        const deletedImagesContainer = document.getElementById('deleted-existing-images-container');

        removeExistingBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // We pull the input name dynamically so PHP knows if it's the main image or a gallery array
                const inputName = this.getAttribute('data-input-name');
                const imageName = this.getAttribute('data-image');
                const wrapper = this.closest('.existing-image-wrapper');

                // Hide the image from the UI
                wrapper.style.display = 'none';

                // Create a hidden input for PHP
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = inputName;
                hiddenInput.value = imageName;

                deletedImagesContainer.appendChild(hiddenInput);
            });
        });

    });
</script>
