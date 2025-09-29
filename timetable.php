<?php
// Path to your PDF file
$filePath = 'uploads/Timetable/TimeTable.pdf'; 
$fileName = 'TimeTable.pdf'; // Desired filename for the browser

if (!file_exists($filePath)) {
    // Handle case where file is not found
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>School Timetable</title>
        <link rel="icon" href="assets/images/delsu.png" type="image/x-icon">
        <?php include ('assets/inc/header.php');?>
    </head>
    <body>
        <h3>TimeTable</h3>
        <div class="alert alert-danger mt-4">Timetable Not Found</div>
        <?php include ('assets/inc/footer.php');?>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>School TimeTable</title>
    <link rel="icon" href="assets/images/delsu.png" type="image/x-icon">
    <?php include ('assets/inc/header.php');?>
</head>
<body>
    <h3>TimeTable</h3>
    <iframe src="<?= htmlspecialchars($filePath) ?>" width="100%" height="800px" style="border:none;" class="mt-4"></iframe>
    <?php include ('assets/inc/footer.php');?>
</body>
</html>