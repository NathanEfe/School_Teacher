<?php
include ('../db_connect.php');


$success_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $staff_id = trim($_POST['staff_id']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($email) || empty($staff_id) || empty($password) || empty($confirm_password)) {
        $error_msg = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM teacher_register WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error_msg = "Email already exists!";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO teacher_register (email,staff_id, password_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email,$staff_id, $password_hash);

            if ($stmt->execute()) {
                $inserted_id = $stmt->insert_id;
                $generated_id = "U" . str_pad($inserted_id, 4, "0", STR_PAD_LEFT);

                $update_stmt = $conn->prepare("UPDATE teacher_register SET user_id = ? WHERE id = ?");
                $update_stmt->bind_param("si", $generated_id, $inserted_id);
                $update_stmt->execute();

                $success_msg = "Registration successful! Kindly <a href='login.php'>Login Here</a>";
            } else {
                $error_msg = "Error: " . $stmt->error;
            }
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
<title>Register</title>

<link rel="shortcut icon" href="assets/img/delsu.png">

<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">

<link rel="stylesheet" href="assets/plugins/feather/feather.css">

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
<h1>Sign Up To Delsu Staff School</h1>
<p class="account-subtitle">Enter details to create your account</p>

<form action="register.php" method="post" >
<div class="form-group">
<label>Email <span class="login-danger">*</span></label>
<input class="form-control" type="email" required name="email">
<span class="profile-views"><i class="fas fa-envelope"></i></span>
</div>
<div class="form-group">
<label>Staff ID <span class="login-danger">*</span></label>
<input class="form-control" type="text" required name="staff_id">
<span class="profile-views"><i class="fas fa-user"></i></span>
</div>
<div class="form-group">
<label>Password <span class="login-danger">*</span></label>
<input class="form-control pass-input" type="password" name="password" required>
<span class="profile-views feather-eye toggle-password"></span>
</div>
<div class="form-group">
<label>Confirm password <span class="login-danger">*</span></label>
<input class="form-control pass-confirm" type="password" name="confirm_password" required >
<span class="profile-views feather-eye reg-toggle-password"></span>
</div>
<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success"><?php echo $success_msg; ?></div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
<?php endif; ?>


<div class=" dont-have">Already Registered? <a href="login.php">Login</a></div>
<div class="form-group mb-0">
<button class="btn btn-primary btn-block" type="submit">Register</button>
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