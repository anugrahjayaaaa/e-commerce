<script>
    /**
     * @file overview: Bulk action UI logic for item lists.
     * @description Manages the state of "select all" and individual checkboxes, updates UI counters, and handles bulk deletion form submission.
     * @date 2026-07-15
     */

    document.addEventListener('DOMContentLoaded', () => {
        // ---------------------------------------------------------
        // 1. CONFIGURATION & DOM ELEMENTS
        // ---------------------------------------------------------
        // Storing elements in an object prevents repeated DOM queries and avoids "magic strings"
        const UI_ELEMENTS = {
            selectAllCheckbox: document.getElementById('select-all'),
            itemCheckboxes: document.querySelectorAll('.checkbox'),
            bulkDeleteBtn: document.getElementById('bulk-delete-btn'),
            selectedCountDisplay: document.getElementById('selected-count'),
            bulkActionForm: document.getElementById('bulk-action-form')
        };

        const CSS_CLASSES = {
            hidden: 'hidden'
        };

        // ---------------------------------------------------------
        // 2. INITIALIZATION
        // ---------------------------------------------------------
        initBulkActions();

        /**
         * Initializes event listeners for the bulk action functionality.
         * Fails gracefully if required DOM elements are missing on the page.
         */
        function initBulkActions() {
            // Graceful error handling: Prevent null reference errors if script is loaded on a page without these elements
            if (!UI_ELEMENTS.selectAllCheckbox || !UI_ELEMENTS.bulkDeleteBtn || !UI_ELEMENTS.bulkActionForm) {
                console.warn('[UI WARN] Bulk action DOM elements not found. Initialization aborted.');
                return;
            }

            // Using named functions instead of anonymous functions for cleaner stack traces during debugging
            UI_ELEMENTS.selectAllCheckbox.addEventListener('change', handleSelectAllChange);

            UI_ELEMENTS.itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', handleIndividualCheckboxChange);
            });

            UI_ELEMENTS.bulkDeleteBtn.addEventListener('click', handleBulkDeleteClick);
        }

        // ---------------------------------------------------------
        // 3. EVENT HANDLERS & LOGIC
        // ---------------------------------------------------------

        /**
         * Toggles all item checkboxes based on the "Select All" checkbox state.
         * 
         * @param {Event} event - The change event triggered by the select all checkbox.
         */
        function handleSelectAllChange(event) {
            const isChecked = event.target.checked;

            UI_ELEMENTS.itemCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });

            updateBulkActionUI();
        }

        /**
         * Triggers a UI update when an individual checkbox is manually toggled.
         */
        function handleIndividualCheckboxChange() {
            updateBulkActionUI();
        }

        /**
         * Calculates the number of selected items and updates the counter text and button visibility.
         */
        function updateBulkActionUI() {
            // Query only the checked checkboxes dynamically
            const checkedCount = document.querySelectorAll('.checkbox:checked').length;

            // Update counter safely
            if (UI_ELEMENTS.selectedCountDisplay) {
                UI_ELEMENTS.selectedCountDisplay.textContent = checkedCount;
            }

            if (checkedCount > 0) {
                UI_ELEMENTS.bulkDeleteBtn.classList.remove(CSS_CLASSES.hidden);
            } else {
                UI_ELEMENTS.bulkDeleteBtn.classList.add(CSS_CLASSES.hidden);
                // Revert 'Select All' to unchecked state if user manually unchecked all items
                UI_ELEMENTS.selectAllCheckbox.checked = false;
            }
        }

        /**
         * Prompts the user for confirmation before submitting the bulk delete form.
         * 
         * @param {Event} event - The click event from the bulk delete button.
         */
        function handleBulkDeleteClick(event) {
            // Prevent default behavior to ensure form doesn't submit before confirmation
            event.preventDefault();

            const checkedCount = document.querySelectorAll('.checkbox:checked').length;

            // Guard clause: Do nothing if somehow clicked when count is 0
            if (checkedCount === 0) return;

            // TODO: Replace standard confirm() with a custom UI modal for better user experience
            const isConfirmed = confirm(`Are you sure you want to delete ${checkedCount} selected items?`);

            if (isConfirmed) {
                UI_ELEMENTS.bulkActionForm.submit();
            }
        }
    });
</script>
