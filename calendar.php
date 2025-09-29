<?php
$filePath = 'uploads/SchoolCalendar/SchoolCalendar.pdf'; 
$fileName = 'SchoolCalendar.pdf';

if (!file_exists($filePath)) {
    // Handle case where file is not found
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>School Calendar</title>
        <link rel="icon" href="assets/images/delsu.png" type="image/x-icon">
        <?php include ('assets/inc/header.php');?>
    </head>
    <body>
        <h3>Calendar</h3>
        <p>File not found.</p>
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
    <title>School Calendar</title>
    <link rel="icon" href="assets/images/delsu.png" type="image/x-icon">
    <?php include ('assets/inc/header.php');?>
</head>
<body>
    <h3>Calendar</h3>
    <iframe src="<?= htmlspecialchars($filePath) ?>" width="100%" height="800px" style="border:none;" class="mt-4"></iframe>
    <?php include ('assets/inc/footer.php');?>
</body>
</html>