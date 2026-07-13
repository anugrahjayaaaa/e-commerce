<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ===================================================
        // 2. AUTO-SLUG GENERATOR LOGIC (GENERAL)
        // ===================================================
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        // Only run if both name and slug inputs exist on the page
        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function() {
                const name = this.value;

                // Generate the slug in real-time
                const slug = name.toLowerCase()
                    .trim() // Remove whitespace from both ends
                    .replace(/[^a-z0-9 -]/g, '') // Remove invalid characters
                    .replace(/\s+/g, '-') // Replace spaces with hyphens
                    .replace(/-+/g, '-'); // Replace multiple hyphens with a single one

                slugInput.value = slug;
            });
        }
    });
</script>
