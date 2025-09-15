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
<h1>Reset Password</h1>
<p class="account-subtitle">Let Us Help You</p>

<form action="send_reset_email.php" method="POST">
<div class="form-group">
<label>Enter your registered email address <span class="login-danger">*</span></label>
<input class="form-control" type="email" name ="email" required>
<span class="profile-views"><i class="fas fa-envelope"></i></span>
</div>
<div class="form-group">
<button class="btn btn-primary btn-block" type="submit">Reset My Password</button>
</div>
</form>

<div class="form-group mb-0">
    <a href="login.php" class="text-light">
        <button class="btn btn-primary primary-reset btn-block" type="submit">Back to Login</button>

    </a> 
</div>

<p class="account-subtitle text-center mt-4">Delsu Staff School</p>


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