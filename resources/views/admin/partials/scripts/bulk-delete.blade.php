<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.checkbox');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        const selectedCount = document.getElementById('selected-count');
        const bulkActionForm = document.getElementById('bulk-action-form');

        // Toggle allcheckboxes
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkButton();
        });

        // Toggle individual checkbox
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkButton);
        });

        function updateBulkButton() {
            const checkedCount = document.querySelectorAll('.checkbox:checked').length;
            selectedCount.textContent = checkedCount;

            if (checkedCount > 0) {
                bulkDeleteBtn.classList.remove('hidden');
            } else {
                bulkDeleteBtn.classList.add('hidden');
                selectAll.checked = false;
            }
        }

        // Bulk delete confirmation
        bulkDeleteBtn.addEventListener('click', () => {
            const count = document.querySelectorAll('.checkbox:checked').length;;
            if (confirm(`Are you sure you want to delete ${count} selected items?`)) {
                bulkActionForm.submit();
            }
        });
    });
</script>
