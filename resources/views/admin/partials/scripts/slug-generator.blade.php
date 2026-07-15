<script>
    /**
     * @file overview: Real-time auto-slug generator.
     * @description Listens to input changes on a name field and dynamically generates a URL-friendly slug in real-time.
     * @author Frontend Team
     * @date 2026-07-15
     */

    document.addEventListener('DOMContentLoaded', () => {
        // ---------------------------------------------------------
        // 1. CONFIGURATION & DOM ELEMENTS
        // ---------------------------------------------------------
        const UI_ELEMENTS = {
            nameInput: document.getElementById('name'),
            slugInput: document.getElementById('slug')
        };

        // ---------------------------------------------------------
        // 2. INITIALIZATION
        // ---------------------------------------------------------
        initSlugGenerator();

        /**
         * Initializes the slug generator by attaching event listeners.
         * Fails gracefully if the required input fields are not present on the current page.
         */
        function initSlugGenerator() {
            if (!UI_ELEMENTS.nameInput || !UI_ELEMENTS.slugInput) {
                return; // Abort silently if elements don't exist
            }

            UI_ELEMENTS.nameInput.addEventListener('input', handleNameInput);
        }

        // ---------------------------------------------------------
        // 3. EVENT HANDLERS
        // ---------------------------------------------------------

        /**
         * Handles the input event, generates the slug, and updates the target input field.
         * 
         * @param {Event} event - The input event triggered by the user typing.
         */
        function handleNameInput(event) {
            const rawName = event.target.value;
            const formattedSlug = generateSlug(rawName);

            UI_ELEMENTS.slugInput.value = formattedSlug;
        }

        // ---------------------------------------------------------
        // 4. DATA PROCESSING (PURE FUNCTIONS)
        // ---------------------------------------------------------

        /**
         * Converts a raw string into a URL-friendly slug format.
         * This is a pure function: it only relies on its input and produces no side effects.
         * 
         * @param {string} text - The raw string to be converted.
         * @returns {string} The formatted slug.
         */
        function generateSlug(text) {
            // Guard clause for empty or undefined input
            if (!text) return '';

            return text.toLowerCase()
                .trim() // Remove whitespace from both ends
                .replace(/[^a-z0-9 -]/g,
                '') // Remove invalid characters (only keep alphanumeric, spaces, and hyphens)
                .replace(/\s+/g, '-') // Replace one or more spaces with a single hyphen
                .replace(/-+/g, '-'); // Collapse multiple consecutive hyphens into a single one
        }
    });
</script>
