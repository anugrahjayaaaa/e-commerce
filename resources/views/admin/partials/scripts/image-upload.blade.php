<script>
    /**
     * @file overview: Single image upload preview and state management.
     * @description Handles local file reading, rendering image previews via Blob URLs, and toggling UI states dynamically based on the layout structure.
     * @date 2026-07-15
     */

    document.addEventListener('DOMContentLoaded', () => {
        // ---------------------------------------------------------
        // 1. CONFIGURATION & SELECTORS
        // ---------------------------------------------------------
        const SELECTORS = {
            group: '[data-upload-group]',
            input: '[data-upload="input"]',
            content: '[data-upload="content"]',
            preview: '[data-upload="preview"]',
            removeBtn: '[data-upload="remove"]',
            container: '[data-upload="container"]' // Optional: exists only in complex layouts (e.g., product)
        };

        const CSS_CLASSES = {
            hidden: 'hidden',
            flex: 'flex',
            borderNone: 'border-none',
            bgWhite: 'bg-white'
        };

        // ---------------------------------------------------------
        // 2. INITIALIZATION
        // ---------------------------------------------------------
        initUploadPrevews();

        /**
         * Finds all upload groups on the page and initializes them.
         */
        function initUploadPrevews() {
            const uploadGroups = document.querySelectorAll(SELECTORS.group);

            uploadGroups.forEach(group => {
                initSingleGroup(group);
            });
        }

        /**
         * Binds UI elements and events for a single upload component.
         * 
         * @param {HTMLElement} group - The main wrapper element for the upload component.
         */
        function initSingleGroup(group) {
            // Collect all related UI elements within this specific group
            const ui = {
                input: group.querySelector(SELECTORS.input),
                content: group.querySelector(SELECTORS.content),
                preview: group.querySelector(SELECTORS.preview),
                removeBtn: group.querySelector(SELECTORS.removeBtn),
                container: group.querySelector(SELECTORS.container)
            };

            // Guard clause: Ensure the essential elements exist before attaching listeners
            if (!ui.input || !ui.content || !ui.preview || !ui.removeBtn) {
                console.warn('[UI WARN] Incomplete upload group structure found. Skipping initialization.',
                    group);
                return;
            }

            // Pass the 'ui' object to the handlers so they know which specific elements to manipulate
            ui.input.addEventListener('change', (event) => handleFileChange(event, ui));
            ui.removeBtn.addEventListener('click', (event) => handleRemoveClick(event, ui));
        }

        // ---------------------------------------------------------
        // 3. EVENT HANDLERS & LOGIC
        // ---------------------------------------------------------

        /**
         * Handles file selection, validates the file type, and triggers the preview rendering.
         * 
         * @param {Event} event - The change event from the file input.
         * @param {Object} ui - The DOM elements for this specific upload group.
         */
        function handleFileChange(event, ui) {
            const file = event.target.files[0];

            // Validate that a file exists and is actually an image
            if (!file || !file.type.startsWith('image/')) {
                resetUploadState(ui);
                return;
            }

            // Clean up any previously generated Blob URL to prevent browser memory leaks
            cleanupMemory(ui.preview);

            // Generate a temporary local URL for the image preview
            ui.preview.src = URL.createObjectURL(file);

            toggleActiveState(ui, true);
        }

        /**
         * Handles the removal of the selected file and resets the UI.
         * 
         * @param {Event} event - The click event from the remove button.
         * @param {Object} ui - The DOM elements for this specific upload group.
         */
        function handleRemoveClick(event, ui) {
            event.preventDefault();
            resetUploadState(ui);
        }

        // ---------------------------------------------------------
        // 4. HELPER FUNCTIONS
        // ---------------------------------------------------------

        /**
         * Restores the input and UI back to its initial empty state.
         * 
         * @param {Object} ui - The DOM elements for this specific upload group.
         */
        function resetUploadState(ui) {
            cleanupMemory(ui.preview);

            ui.input.value = '';
            ui.preview.src = '';

            toggleActiveState(ui, false);
        }

        /**
         * Toggles the UI elements between 'empty' and 'preview' modes based on the layout structure.
         * 
         * @param {Object} ui - The DOM elements for this specific upload group.
         * @param {boolean} hasImage - True if an image is selected, false if empty.
         */
        function toggleActiveState(ui, hasImage) {
            if (ui.container) {
                // Complex Layout (e.g., Product Image)
                if (hasImage) {
                    ui.content.classList.add(CSS_CLASSES.hidden);
                    ui.container.classList.remove(CSS_CLASSES.hidden);
                    ui.container.classList.add(CSS_CLASSES.flex);
                } else {
                    ui.container.classList.add(CSS_CLASSES.hidden);
                    ui.container.classList.remove(CSS_CLASSES.flex);
                    ui.content.classList.remove(CSS_CLASSES.hidden);
                }
            } else {
                // Standard/Simple Layout
                const parent = ui.content.parentElement;

                if (hasImage) {
                    parent.classList.add(CSS_CLASSES.borderNone, CSS_CLASSES.bgWhite);
                    ui.preview.classList.remove(CSS_CLASSES.hidden);
                    ui.removeBtn.classList.remove(CSS_CLASSES.hidden);
                } else {
                    // BUG FIX: Changed from add() to remove() so the dashed border comes back on reset
                    parent.classList.remove(CSS_CLASSES.borderNone, CSS_CLASSES.bgWhite);
                    ui.preview.classList.add(CSS_CLASSES.hidden);
                    ui.removeBtn.classList.add(CSS_CLASSES.hidden);
                }
            }
        }

        /**
         * Revokes the Blob object URL to free up browser memory.
         * 
         * @param {HTMLImageElement} previewElement - The image tag holding the preview.
         */
        function cleanupMemory(previewElement) {
            if (previewElement.src && previewElement.src.startsWith('blob:')) {
                URL.revokeObjectURL(previewElement.src);
            }
        }
    });
</script>
