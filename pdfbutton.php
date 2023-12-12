<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Viewer</title>
</head>
<body>
    <button id="viewPdfButton">View PDF</button>

    <script>
        document.getElementById("viewPdfButton").addEventListener("click", function() {
            // Fetch the PDF filename dynamically from generatepdf.php
            fetch('generatepdf.php')
                .then(response => response.text())
                .then(pdfFileName => {
                    // Construct the URL with the dynamic filename
                    window.location.href = "generatepdf.php?filename=" + encodeURIComponent(pdfFileName);
                })
                .catch(error => {
                    console.error('Error fetching PDF filename:', error);
                });
        });
    </script>
</body>
</html>
