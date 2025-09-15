<?php
include ('db_connect.php');

// Get logged in teacher ID from session
$staff_id = $_SESSION['staff_id'] ?? null;
$teacher_name = "";

// Fetch teacher name if logged in
if ($staff_id) {
    $result = $conn->query("SELECT full_name FROM teacher_register WHERE staff_id = '$staff_id'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $teacher_name = $row['full_name'];
    }
}




echo "Welcome" . " " . $teacher_name;
?>



