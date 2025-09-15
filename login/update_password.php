<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>


<?php
require '../db_connect.php';

$token = $_POST['token'] ?? '';

$new_password_raw = $_POST['new_password'] ?? '';

if (strlen($new_password_raw) < 6) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        Password must be at least 6 characters long.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    exit;
}
$new_password = $_POST['new_password'] ?? '';

if (empty($token) || empty($new_password)) {
    die("Invalid request.");
}

// Check if token is valid and not expired
$stmt = $conn->prepare("SELECT email, expires_at FROM teacher_password_resets WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid or expired token.");
}

$row = $result->fetch_assoc();
$email = $row['email'];
$expires_at = $row['expires_at'];

if (strtotime($expires_at) < time()) {
echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <strong>Error:</strong> Token has expired.
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
exit;
}

// Update password in register table
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE teacher_register SET password_hash = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_password, $email);

if ($stmt->execute()) {
    // Delete used token
    $stmt = $conn->prepare("DELETE FROM teacher_password_resets WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
  <strong>Success:</strong> Password updated successfully!
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
} else {
echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <strong>Error:</strong> Failed to update password.
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
}
?>
