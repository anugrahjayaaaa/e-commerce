<script>
    // Grab required global modal elements
    const deleteModal = document.getElementById('deleteModal');
    const globalDeleteForm = document.getElementById('globalDeleteForm');
    const modalTitle = document.getElementById('dynamic-modal-title');
    const itemNameSpan = document.getElementById('dynamic-item-name');
    const cancelBtn = document.getElementById('cancelDeleteBtn');

    /**
     * Core Universal Function to open modal and assign parameters dynamically
     * @param {string} itemName - The display name of the object (e.g., "Samsung")
     * @param {string} deleteUrl - The actual Laravel named route URI (e.g., "/brands/12")
     * @param {string} type - Page context identifier (e.g., "brand", "category")
     */
    function openDeleteModal(itemName, deleteUrl, type = 'item') {
        if (!deleteModal || !globalDeleteForm) return;

        // 1. Set the form action dynamically to the specified Laravel route URL
        globalDeleteForm.setAttribute('action', deleteUrl);

        // 2. Format the title header to match Capital Case capitalization (e.g., brand -> Brand)
        const capitalizedType = type.charAt(0).toUpperCase() + type.slice(1);

        // 3. Update UI string literals inside the modal wrapper
        modalTitle.textContent = `Delete ${capitalizedType}`;
        itemNameSpan.textContent = itemName || `this ${type}`;

        // 4. Fire up the modal layout by removing the 'hidden' token class
        deleteModal.classList.remove('hidden');
    }

    /**
     * FUNCTION ALIASES GROUP
     * Backward compatibility functions to avoid updating existing legacy onclick parameters.
     */
    function deleteBrand(buttonElement, brandName, brandId) {
        // Generate the URL endpoint pattern dynamically matching your Laravel routes configuration
        const deleteUrl = `/brands/${brandId}`;
        openDeleteModal(brandName, deleteUrl, 'brand');
    }

    function deleteCategory(buttonElement, categoryName, categoryId) {
        const deleteUrl = `/categories/${categoryId}`;
        openDeleteModal(categoryName, deleteUrl, 'category');
    }


    /**
     * MODAL CLOSE LOGIC INTERFACES
     */
    function closeModal() {
        if (deleteModal) {
            deleteModal.classList.add('hidden');
            globalDeleteForm.setAttribute('action', ''); // Reset form endpoint target URL for absolute security
        }
    }

    // Handle dismiss triggers on cancel button click event listener
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    // Handle backdrop container dismiss clicks outside the main structural layout block panel
    if (deleteModal) {
        deleteModal.addEventListener('click', function(event) {
            if (event.target === this || event.target.classList.contains('bg-opacity-75')) {
                closeModal();
            }
        });
    }
</script>
