<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Loop through all elements acting as an upload group
        document.querySelectorAll('[data-upload-group]').forEach(group => {
            const input = group.querySelector('[data-upload="input"]');
            const content = group.querySelector('[data-upload="content"]');
            const preview = group.querySelector('[data-upload="preview"]');
            const removeBtn = group.querySelector('[data-upload="remove"]');
            const container = group.querySelector(
            '[data-upload="container"]'); // Only exists in product layout

            // Safety check to ensure core elements exist
            if (!input || !content || !preview || !removeBtn) return;

            // Handle file selection and display preview
            input.addEventListener('change', function(event) {
                const file = event.target.files[0];

                if (file && file.type.startsWith('image/')) {
                    preview.src = URL.createObjectURL(file);

                    // Toggle visibility based on layout structure
                    if (container) {
                        content.classList.add('hidden');
                        container.classList.remove('hidden');
                        container.classList.add('flex');
                    } else {
                        content.parentElement.classList.add('border-none', 'bg-white');
                        preview.classList.remove('hidden');
                        removeBtn.classList.remove('hidden');
                    }
                } else {
                    resetUploadState();
                }
            });

            // Handle remove button action
            removeBtn.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent accidental form submission
                resetUploadState();
            });

            // Helper function to restore the UI to its default state
            function resetUploadState() {
                // Revoke the object URL to free up memory resources
                if (preview.src.startsWith('blob:')) {
                    URL.revokeObjectURL(preview.src);
                }

                input.value = '';
                preview.src = '';

                // Restore visibility based on layout structure
                if (container) {
                    container.classList.add('hidden');
                    container.classList.remove('flex');
                    content.classList.remove('hidden');
                } else {
                    content.parentElement.classList.add('border-none', 'bg-white');
                    preview.classList.add('hidden');
                    removeBtn.classList.add('hidden');
                }
            }
        });
    });
</script>
