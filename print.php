<button onclick="generatePDF()" class="btn btn-dark">Print</button>
          
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
          
<script>
function generatePDF() {
    // Create a new jsPDF instance
    var pdf = new jsPDF();

    // Add content to the PDF
    pdf.text('RFID Entry and Exit Logs', 20, 20);

    // Get the HTML content of the logs container
    var logsHtml = document.getElementById('logs-container').outerHTML;

    // Generate the PDF from HTML
    pdf.fromHTML(logsHtml, 20, 30, { width: 170 });

    // Save the PDF or open in a new tab
    pdf.save('RFID_Logs.pdf');
}
</script>