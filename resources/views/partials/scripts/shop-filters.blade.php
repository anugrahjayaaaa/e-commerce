<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('form-filter');

        // Guard clause: stopping script execution if the form doesn't exist on the current page
        if (!form) return;

        // ==========================================
        // 1. STANDARD INPUTS (Dropdowns, Checkboxes)
        // ==========================================
        document.querySelectorAll('.auto-submit, .filter-checkbox').forEach(element => {
            element.addEventListener('change', () => {
                form.submit();
            });
        });

        // ==========================================
        // 2. PRICE RANGE SLIDER
        // ==========================================
        const tracker = document.getElementById('range-tracker');
        const minSlider = document.getElementById('range-min');
        const maxSlider = document.getElementById('range-max');

        function updateTracker() {
            if (!tracker || !minSlider || !maxSlider) return;
            const minAttr = parseInt(minSlider.min) || 0;
            const maxAttr = parseInt(minSlider.max) || 1000;
            const leftPercent = ((minSlider.value - minAttr) / (maxAttr - minAttr)) * 100;
            const rightPercent = 100 - (((maxSlider.value - minAttr) / (maxAttr - minAttr)) * 100);
            tracker.style.left = leftPercent + '%';
            tracker.style.right = rightPercent + '%';
        }

        // Initialize slider track visual on page load
        updateTracker();

        let timeout = null;
        document.querySelectorAll('.price-slider').forEach(slider => {
            slider.addEventListener('input', function() {
                if (this.id === 'range-min') document.getElementById('price-min-display')
                    .innerText = this.value;
                if (this.id === 'range-max') document.getElementById('price-max-display')
                    .innerText = this.value;

                if (parseInt(minSlider.value) > parseInt(maxSlider.value)) {
                    if (this.id === 'range-min') minSlider.value = maxSlider.value;
                    if (this.id === 'range-max') maxSlider.value = minSlider.value;
                }

                updateTracker();

                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    form.submit();
                }, 500);
            });
        });
    });
</script>
