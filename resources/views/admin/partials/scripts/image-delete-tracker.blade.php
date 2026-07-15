<script>
    /**
     * @file overview: Existing image removal logic for edit/update forms.
     * @description Handles the UI removal of existing images and dynamically generates hidden inputs to notify the backend of the deletion.
     * @date 2026-07-15
     */

    document.addEventListener('DOMContentLoaded', () => {
        // ---------------------------------------------------------
        // 1. CONFIGURATION & DOM ELEMENTS
        // ---------------------------------------------------------

        // Abstracting CSS classes used for DOM traversal
        const CSS_CLASSES = {
            imageWrapper: '.existing-image-wrapper',
            triggerBtn: '.js-remove-existing-btn'
        };

        const UI_ELEMENTS = {
            removeBtns: document.querySelectorAll(CSS_CLASSES.triggerBtn),
            deletedImagesContainer: document.getElementById('deleted-existing-images-container')
        };

        // ---------------------------------------------------------
        // 2. INITIALIZATION
        // ---------------------------------------------------------
        initImageRemoval();

        /**
         * Initializes event listeners for image removal buttons.
         * Fails gracefully if the script is loaded on a Create page instead of an Edit page.
         */
        function initImageRemoval() {
            // Guard clause: If the container doesn't exist, we aren't on the Edit page. Abort safely.
            if (!UI_ELEMENTS.deletedImagesContainer) {
                return;
            }

            UI_ELEMENTS.removeBtns.forEach(btn => {
                btn.addEventListener('click', handleImageRemoval);
            });
        }

        // ---------------------------------------------------------
        // 3. EVENT HANDLERS & LOGIC
        // ---------------------------------------------------------

        /**
         * Handles the click event to hide the image visually and trigger the backend tracker.
         *
         * @param {Event} event - The click event from the removal button.
         */
        function handleImageRemoval(event) {
            const button = event.currentTarget;

            // Using HTML5 dataset API is cleaner than getAttribute
            // This corresponds to data-input-name="..." and data-image="..." in HTML
            const inputName = button.dataset.inputName;
            const imageName = button.dataset.image;

            const wrapper = button.closest(CSS_CLASSES.imageWrapper);

            // Graceful error handling: ensure the wrapper exists before manipulating it
            if (!wrapper) {
                console.warn(
                    '[UI WARN] Parent image wrapper not found. Ensure the HTML structure matches the expected format.'
                    );
                return;
            }

            // HACK: We use display 'none' instead of node.remove() 
            // This prevents layout shifts from breaking immediately and leaves the door open for an "Undo" feature later.
            wrapper.style.display = 'none';

            appendHiddenTrackerInput(inputName, imageName);
        }

        /**
         * Generates a hidden input field and appends it to the container so PHP can process the deletion.
         *
         * @param {string} name - The name attribute for the hidden input array.
         * @param {string} value - The filename or ID of the deleted image.
         */
        function appendHiddenTrackerInput(name, value) {
            // Guard clause for missing data
            if (!name || !value) {
                console.warn('[UI WARN] Missing data attributes for image deletion tracker.');
                return;
            }

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = name;
            hiddenInput.value = value;

            UI_ELEMENTS.deletedImagesContainer.appendChild(hiddenInput);
        }
    });
</script>
