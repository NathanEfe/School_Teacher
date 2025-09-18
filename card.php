<?php
include ('db_connect.php');
$result = $conn->query("SELECT COUNT(*) AS total_students FROM  jss2_students_records");
$row = $result -> fetch_assoc();
$total_students = $row['total_students'];
?>


