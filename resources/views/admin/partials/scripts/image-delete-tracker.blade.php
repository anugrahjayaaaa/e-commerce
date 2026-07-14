<script>
    document.addEventListener('DOMContentLoaded', function() {
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
