
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>Forgot Password</title>

<link rel="shortcut icon" href="assets/img/delsu.png">

<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">

<link rel="stylesheet" href="assets/plugins/feather/feather.css">

<link rel="stylesheet" href="assets/plugins/icons/flags/flags.css">

<link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">

<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<script src="assets/js/jquery-3.6.0.min.js"></script>

<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/feather.min.js"></script>

<script src="assets/js/script.js"></script>
</body>
</html>


<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; 
require '../db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(32)); // unique secure token
    $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // 1. Check if email exists in teacher_register table
    $stmt = $conn->prepare("SELECT id FROM teacher_register WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
    echo '<div class="mt-4 container alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Error!</strong> Email not found.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
exit;
    }

    // 2. Delete any existing tokens for this email (optional cleanup)
    $stmt = $conn->prepare("DELETE FROM teacher_password_resets WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    // 3. Insert new token
    $stmt = $conn->prepare("INSERT INTO teacher_password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $token, $expires_at);
    $stmt->execute();

    // 4. Send Email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'ictdelsu@delsu.edu.ng'; 
        $mail->Password = 'qynl euel bgqs khwz'; // App password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('ictdelsu@delsu.edu.ng', 'Hospital Management System');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Link';
        $mail->Body = "Click <a href='http://localhost/school/teacher/login/reset_password.php?token=$token'>here</a> to reset your password. This link will expire in 1 hour.";

        $mail->send();
         echo '<div  class="mt-4 container alert alert-success alert-dismissible fade show" role="alert">
    <strong>Success!</strong> Reset Link Sent.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    } catch (Exception $e) {

echo '<div class="mt-4 container alert alert-danger alert-dismissible fade show" role="alert">
  <strong>Email not sent!</strong> Error: ' . $mail->ErrorInfo . '
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
    }
}
?>



