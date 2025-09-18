<?php
session_start();

include ('../db_connect.php');

$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login_input = trim($_POST["login_input"]);
    $password = $_POST["password"];

    if (empty($login_input) || empty($password)) {
        $error_msg = "Both fields are required!";
    } else {
        $stmt = $conn->prepare("SELECT id, user_id, email, staff_id, password_hash FROM teacher_register WHERE staff_id = ? OR email = ?");
        $stmt->bind_param("ss", $login_input, $login_input);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $user_id, $db_email, $db_staff_id, $db_password_hash);
            $stmt->fetch();

            if (password_verify($password, $db_password_hash)) {
                $_SESSION["user_id"] = $user_id;
                $_SESSION["staff_id"] = $db_staff_id;
                $success_msg = "Login successful! Redirecting...";
                header("refresh:2;url=../index.php");
            } else {
                $error_msg = "Incorrect password!";
            }
        } else {
            $error_msg = "Staff ID or Email not found!";
        }
        $stmt->close();
    }

    $conn->close();
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>Login</title>

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

<div class="main-wrapper login-body">
<div class="login-wrapper">
<div class="container">
<div class="loginbox">
<div class="login-left justify-content-center">
<img class="img-fluid h-50 p-4" src="assets/img/delsu.png" alt="Logo">
</div>
<div class="login-right">
<div class="login-right-wrap">
<img class="img-fluid" src="assets/img/delsulogo.jpg" alt="Logo">
<h1>Welcome to Delsu Staff School</h1>
<!-- <p class="account-subtitle">Need an account? <a href="register.php">Sign Up</a></p> -->
<h2>Sign in</h2>

<form action="login.php" method="post">
<div class="form-group">
    <label>Email or Staff ID <span class="login-danger">*</span></label>
    <input class="form-control" type="text" name="login_input" required>
    <span class="profile-views"><i class="fas fa-user-circle"></i></span>
</div>

<div class="form-group">
<label>Password <span class="login-danger">*</span></label>
<input class="form-control pass-input" type="password" name="password" required>
<span class="profile-views feather-eye toggle-password"></span>
</div>
<div class="forgotpass">
<div class="remember-me">
<label class="custom_check mr-2 mb-0 d-inline-flex remember-me"> Remember me
<input type="checkbox" name="radio">
<span class="checkmark"></span>
</label>
</div>
<a href="forgot-password.php">Forgot Password?</a>
</div>
<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success"><?php echo $success_msg; ?></div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
<?php endif; ?>

<div class="form-group">
<button class="btn btn-primary btn-block" type="submit">Login</button>
</div>

<p class="account-subtitle text-center mt-4">Delsu Staff School</p>

</form>

</div>
</div>
</div>
</div>
</div>
</div>


<script src="assets/js/jquery-3.6.0.min.js"></script>

<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/feather.min.js"></script>

<script src="assets/js/script.js"></script>
</body>
</html>