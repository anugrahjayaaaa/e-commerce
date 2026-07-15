<script>
    /**
     * @file overview: Multiple image gallery upload and preview logic.
     * @description Handles multiple file selection, duplicate prevention, dynamic preview rendering, and syncing file state using the DataTransfer API.
     * @author Frontend Team
     * @date 2026-07-15
     */

    document.addEventListener('DOMContentLoaded', () => {
        // ---------------------------------------------------------
        // 1. CONFIGURATION & STATE
        // ---------------------------------------------------------
        const UI_ELEMENTS = {
            input: document.getElementById('upload-images'),
            previewContainer: document.getElementById('gallery-preview-container')
        };

        // State management: Holds the validated File objects
        let selectedFiles = [];

        // State management: Tracks active Blob URLs for memory cleanup
        let activeObjectUrls = [];

        // ---------------------------------------------------------
        // 2. INITIALIZATION
        // ---------------------------------------------------------
        initGalleryUpload();

        function initGalleryUpload() {
            if (!UI_ELEMENTS.input || !UI_ELEMENTS.previewContainer) {
                return;
            }

            UI_ELEMENTS.input.addEventListener('change', handleFilesSelected);
        }

        // ---------------------------------------------------------
        // 3. EVENT HANDLERS
        // ---------------------------------------------------------

        /**
         * Processes new files from the input, filters out invalid or duplicate files, and triggers a UI update.
         * 
         * @param {Event} event - The change event from the file input.
         */
        function handleFilesSelected(event) {
            const incomingFiles = Array.from(event.target.files);

            incomingFiles.forEach(file => {
                if (!file.type.startsWith('image/')) return;

                // Check for duplicates based on name and size
                const isDuplicate = selectedFiles.some(f => f.name === file.name && f.size === file
                    .size);

                if (!isDuplicate) {
                    selectedFiles.push(file);
                }
            });

            updateGalleryStateAndUI();
        }

        /**
         * Handles the removal of a specific image from the gallery array.
         * 
         * @param {number} indexToRemove - The array index of the file to remove.
         */
        function handleRemoveImage(indexToRemove) {
            // Remove the file from the state array
            selectedFiles.splice(indexToRemove, 1);

            // Re-sync and re-render
            updateGalleryStateAndUI();
        }

        // ---------------------------------------------------------
        // 4. CORE LOGIC (STATE & DOM)
        // ---------------------------------------------------------

        /**
         * Syncs the JavaScript array with the HTML input element and triggers DOM re-rendering.
         */
        function updateGalleryStateAndUI() {
            syncFilesToInput();
            renderPreviews();
        }

        /**
         * Uses the DataTransfer API to update the actual <input type="file"> 
         * so that the backend receives the correct files upon form submission.
         */
        function syncFilesToInput() {
            const dataTransfer = new DataTransfer();

            selectedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });

            UI_ELEMENTS.input.files = dataTransfer.files;
        }

        /**
         * Clears the UI and re-renders image previews based on the current state array.
         */
        function renderPreviews() {
            // 1. Memory Cleanup: Revoke all previous Blob URLs before clearing the DOM
            cleanupMemory();

            // 2. Clear DOM
            UI_ELEMENTS.previewContainer.innerHTML = '';

            // 3. Re-render DOM
            selectedFiles.forEach((file, index) => {
                const objectUrl = URL.createObjectURL(file);
                activeObjectUrls.push(objectUrl); // Track for future cleanup

                const previewElement = createPreviewElement(objectUrl, index);
                UI_ELEMENTS.previewContainer.appendChild(previewElement);
            });
        }

        // ---------------------------------------------------------
        // 5. DOM FACTORY & HELPERS
        // ---------------------------------------------------------

        /**
         * Creates the HTML structure for a single gallery preview item.
         * 
         * @param {string} objectUrl - The Blob URL of the image.
         * @param {number} index - The current index of the file in the state array.
         * @returns {HTMLElement} The constructed div wrapper containing the image and remove button.
         */
        function createPreviewElement(objectUrl, index) {
            // Create wrapper
            const wrapper = document.createElement('div');
            wrapper.className =
                'relative h-20 bg-gray-50 rounded border border-blue-200 flex items-center justify-center overflow-hidden group shadow-sm';

            // Create image
            const img = document.createElement('img');
            img.src = objectUrl;
            img.className = 'w-full h-full object-cover';

            // Create remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className =
                'absolute top-1 right-1 bg-red-500 text-white rounded-md w-6 h-6 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity focus:outline-none shadow-md';
            removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';

            // Attach event listener directly to the button
            removeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                handleRemoveImage(index);
            });

            // Assemble
            wrapper.appendChild(img);
            wrapper.appendChild(removeBtn);

            return wrapper;
        }

        /**
         * Revokes all active Blob URLs to prevent memory leaks during re-renders.
         */
        function cleanupMemory() {
            activeObjectUrls.forEach(url => {
                URL.revokeObjectURL(url);
            });

            // Reset the tracker array
            activeObjectUrls = [];
        }
    });
</script>
