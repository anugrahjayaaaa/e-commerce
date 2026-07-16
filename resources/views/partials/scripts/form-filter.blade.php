<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('form-filter');
        document.querySelectorAll('.auto-submit').forEach(element => {
            element.addEventListener('change', () => { 
                form.submit();
            });
        });
    });
</script>
