<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('form-filter');
        document.querySelectorAll('.auto-submit, .filter-checkbox').forEach(element => {
            element.addEventListener('change', () => { 
                form.submit();
            });
        });
    });
</script>
