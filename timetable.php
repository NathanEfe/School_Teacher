

<?php
// Path to your PDF file
$filePath = 'uploads/Timetable/TimeTable.pdf'; 
$fileName = 'TimeTable.pdf'; // Desired filename for the browser

// Check if the file exists
if (file_exists($filePath)) {
    // Set headers for inline display
    header('Content-type: application/pdf');
    header('Content-Disposition: inline; filename="' . $fileName . '"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . filesize($filePath));

    // Output the PDF content
    @readfile($filePath);
} else {
    // Handle case where file is not found
    echo 'Error: PDF file not found.';
}
?>
<?php include ('assets/inc/header.php');?>
<h3>TimeTable</h3>

<?php include ('assets/inc/footer.php');?>