<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ==========================================
        // 3. MULTIPLE NEW IMAGE UPLOAD (GALLERY)
        // ==========================================
        const galleryInput = document.getElementById('upload-images');

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
    });
</script>
