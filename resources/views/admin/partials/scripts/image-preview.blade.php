<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ===================================================
        // 1. IMAGE PREVIEW & REMOVE LOGIC (GENERAL)
        // ===================================================
        const imageInput = document.getElementById('upload-image');
        const uploadContent = document.getElementById('upload-content');
        const imagePreview = document.getElementById('image-preview');
        const removeBtn = document.getElementById('remove-image-btn');

        // Only run if all image-related elements exist on the page
        if (imageInput && uploadContent && imagePreview && removeBtn) {

            // Handle file selection
            imageInput.addEventListener('change', function(event) {
                const file = event.target.files[0];

                if (file && file.type.startsWith('image/')) {
                    // Display the image preview
                    imagePreview.src = URL.createObjectURL(file);
                    uploadContent.classList.add('hidden');
                    imagePreview.classList.remove('hidden');
                    removeBtn.classList.remove('hidden');
                } else {
                    // Reset UI if the file is invalid or selection is canceled
                    resetImageState();
                }
            });

            // Handle remove button click
            removeBtn.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent accidental form submission
                resetImageState();
            });

            // Helper function to restore the UI to its default state
            function resetImageState() {
                imageInput.value = ''; // Clear the actual file input value
                imagePreview.src = '';

                uploadContent.classList.remove('hidden');
                imagePreview.classList.add('hidden');
                removeBtn.classList.add('hidden');
            }
        }
    });
</script>
