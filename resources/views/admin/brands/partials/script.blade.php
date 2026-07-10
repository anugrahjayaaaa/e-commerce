<script>
    document.addEventListener('DOMContentLoaded', function() {
        const logoInput = document.getElementById('brand-image');
        const uploadContent = document.getElementById('upload-content');
        const imagePreview = document.getElementById('image-preview');
        const removeBtn = document.getElementById('remove-logo-btn');

        // Handle file selection
        logoInput.addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (file && file.type.startsWith('image/')) {
                // Show preview
                imagePreview.src = URL.createObjectURL(file);
                uploadContent.classList.add('hidden');
                imagePreview.classList.remove('hidden');
                removeBtn.classList.remove('hidden'); // Show the 'X' button
            } else {
                // Invalid file or canceled
                resetImageState();
            }
        });

        // Handle remove button click
        removeBtn.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent form submission if inside a form
            resetImageState();
        });

        // Helper function to reset the UI to its default state
        function resetImageState() {
            logoInput.value = ''; // Clear the actual file input
            imagePreview.src = '';

            // Toggle visibility classes back to default
            uploadContent.classList.remove('hidden');
            imagePreview.classList.add('hidden');
            removeBtn.classList.add('hidden');
        }

        // --- Slug Auto-Generation ---
        const brandNameInput = document.getElementById('name');
        const brandSlugInput = document.getElementById('slug');

        brandNameInput.addEventListener('input', function() {
            const name = this.value;

            // Generate the slug
            const slug = name.toLowerCase()
                .trim() // Remove whitespace from both ends
                .replace(/[^a-z0-9 -]/g, '') // Remove invalid characters
                .replace(/\s+/g, '-') // Replace spaces with hyphens
                .replace(/-+/g, '-'); // Replace multiple hyphens with a single one

            brandSlugInput.value = slug;
        });
    });
</script>
