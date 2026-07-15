<script>
    /**
     * @file overview: Universal delete confirmation modal (Unobtrusive version).
     * @description Handles delete modal interactions dynamically using HTML5 data attributes.
     *              Eliminates the need for individual item functions (Brand, Category, Product).
     * @author Frontend Team
     * @date 2026-07-15
     */

    document.addEventListener('DOMContentLoaded', () => {
        // ---------------------------------------------------------
        // 1. CONFIGURATION & DOM ELEMENTS
        // ---------------------------------------------------------
        const CSS_CLASSES = {
            hidden: 'hidden',
            backdropVariant: 'bg-opacity-75'
        };

        // Storing DOM elements once the document is ready
        const UI_ELEMENTS = {
            modal: document.getElementById('deleteModal'),
            form: document.getElementById('globalDeleteForm'),
            title: document.getElementById('dynamic-modal-title'),
            itemNameDisplay: document.getElementById('dynamic-item-name'),
            cancelBtn: document.getElementById('cancelDeleteBtn'),
            // Query all buttons configured to open this modal
            deleteTriggers: document.querySelectorAll('.js-delete-trigger')
        };

        // ---------------------------------------------------------
        // 2. INITIALIZATION
        // ---------------------------------------------------------
        initModal();

        /**
         * Checks if modal components exist and attaches necessary events.
         */
        function initModal() {
            // Graceful error handling: Abort if modal HTML is missing on this page
            if (!UI_ELEMENTS.modal || !UI_ELEMENTS.form) {
                console.info('[UI INFO] Global delete modal not present on this page.');
                return;
            }

            attachEventListeners();
        }

        /**
         * Binds click events to UI triggers and dismiss areas.
         */
        function attachEventListeners() {
            // Attach click event to every delete button found
            UI_ELEMENTS.deleteTriggers.forEach(trigger => {
                trigger.addEventListener('click', handleOpenModal);
            });

            if (UI_ELEMENTS.cancelBtn) {
                UI_ELEMENTS.cancelBtn.addEventListener('click', handleCloseModal);
            }

            UI_ELEMENTS.modal.addEventListener('click', handleBackdropClick);
        }

        // ---------------------------------------------------------
        // 3. CORE EVENT HANDLERS
        // ---------------------------------------------------------

        /**
         * Extracts configuration from the clicked HTML element and displays the modal.
         * 
         * @param {Event} event - The click event object.
         */
        function handleOpenModal(event) {
            // 'currentTarget' safely gets the button element, even if the inner <i> icon is clicked
            const button = event.currentTarget;

            // Extract parameters automatically from HTML 'data-*' attributes
            const itemName = button.dataset.name;
            const deleteUrl = button.dataset.url;
            const itemType = button.dataset.type || 'item';

            const capitalizedType = itemType.charAt(0).toUpperCase() + itemType.slice(1);

            // Bind the exact endpoint to the form action
            UI_ELEMENTS.form.setAttribute('action', deleteUrl);

            // Update Typography
            if (UI_ELEMENTS.title) {
                UI_ELEMENTS.title.textContent = `Delete ${capitalizedType}`;
            }

            if (UI_ELEMENTS.itemNameDisplay) {
                UI_ELEMENTS.itemNameDisplay.textContent = itemName || `this ${itemType}`;
            }

            // Display the modal
            UI_ELEMENTS.modal.classList.remove(CSS_CLASSES.hidden);
        }

        /**
         * Hides the modal panel and sanitizes the form state.
         */
        function handleCloseModal() {
            UI_ELEMENTS.modal.classList.add(CSS_CLASSES.hidden);

            // HACK: Resetting the action URL prevents accidental form submission to the previous URL
            UI_ELEMENTS.form.setAttribute('action', '');
        }

        /**
         * Dismisses the modal when clicking on the dark background overlay.
         * 
         * @param {Event} event - The click event object.
         */
        function handleBackdropClick(event) {
            const isSelf = event.target === UI_ELEMENTS.modal;
            const isBackdropOverlay = event.target.classList.contains(CSS_CLASSES.backdropVariant);

            if (isSelf || isBackdropOverlay) {
                handleCloseModal();
            }
        }
    });
</script>
