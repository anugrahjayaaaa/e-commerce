<script>
    /**
     * @file overview: Print utility for specific DOM elements.
     * @description Creates a temporary iframe to print a specific element without destroying the main page state.
     * @author Frontend Team
     * @date 2026-07-15
     */

    (function() {
        /**
         * Prints the contents of a specific HTML element by ID.
         * 
         * @param {string} elementId - The ID of the element to print.
         */
        window.printElement = function(elementId) {
            const element = document.getElementById(elementId);

            if (!element) {
                console.error(`[PRINT ERROR] Element with ID '${elementId}' not found.`);
                return;
            }

            // 1. Create a hidden iframe
            const iframe = document.createElement('iframe');
            iframe.style.position = 'absolute';
            iframe.style.width = '0px';
            iframe.style.height = '0px';
            iframe.style.border = 'none';
            document.body.appendChild(iframe);

            // 2. Inject content and necessary styles
            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(`
            <html>
                <head>
                    <title>Print</title>
                    ${getStyleSheets()}
                </head>
                <body>
                    ${element.innerHTML}
                </body>
            </html>
        `);
            doc.close();

            // 3. Trigger Print
            // We use a small timeout to ensure styles are loaded before print dialog appears
            setTimeout(() => {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();

                // 4. Cleanup
                document.body.removeChild(iframe);
            }, 500);
        };

        /**
         * Helper to clone current page stylesheets into the iframe.
         * This ensures the printed content looks exactly like the web version.
         * 
         * @returns {string} String of <link> tags.
         */
        function getStyleSheets() {
            let styles = '';
            document.querySelectorAll('link[rel="stylesheet"], style').forEach(style => {
                styles += style.outerHTML;
            });
            return styles;
        }
    })();
</script>
