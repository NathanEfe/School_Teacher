<?php
require 'db_connect.php';

$class_id = $_GET['class_id'] ?? '';

if ($class_id != '') {
    $query = $conn->prepare("SELECT student_id, name FROM jss2_students_records WHERE class_id = ? ORDER BY student_id");
    $query->bind_param("s", $class_id);
    $query->execute();
    $result = $query->get_result();

    echo '<option value="">All</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="'.$row['student_id'].'">'.htmlspecialchars($row['name']).' ('.$row['student_id'].')</option>';
    }
} else {
    echo '<option value="">All</option>';
}
?>
