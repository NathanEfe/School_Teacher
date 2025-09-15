<?php
require '../db_connect.php';

$token = $_GET['token'] ?? '';

$stmt = $conn->prepare("SELECT * FROM teacher_password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$showForm = false;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $email = $row['email'];
    $showForm = true;
} else {
    $error = "Invalid or expired token.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

<?php if (isset($error)): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<?php if ($showForm): ?>
  <h3>Reset Your Password</h3>
  <form method="POST" action="update_password.php" class="mt-4">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    
    <div class="mb-3">
      <label for="new_password" class="form-label">New Password (min 6 characters):</label>
      <input type="password" name="new_password" id="new_password" class="form-control" minlength="6" required>
    </div>

    <button type="submit" class="btn btn-primary">Update Password</button>
  </form>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
