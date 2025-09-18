<?php  
include(__DIR__ . '/../../db_connect.php');

$staff_id = $_SESSION['staff_id'];

$stmt = $conn->prepare("SELECT * FROM teachers_register WHERE staff_id = ?");
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>