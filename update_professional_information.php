<?php
error_reporting(0);
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include('db_connect.php');


$success_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id            = $_SESSION['user_id'];
    $job_title          = trim($_POST['job_title']);
    $department         = trim($_POST['department']);
    $license_number     = trim($_POST['license_number']);
    $years_of_experience= intval($_POST['years_of_experience']);
    $qualification      = trim($_POST['qualification']);

    $stmt = $conn->prepare("UPDATE teacher_register SET 
        job_title = ?, department = ?, license_number = ?, 
        years_of_experience = ?, qualification = ?
        WHERE user_id = ?");
    
    $stmt->bind_param("sssiss", $job_title, $department, $license_number, $years_of_experience, $qualification, $user_id);

    if ($stmt->execute()) {
        $success_msg = "Professional information updated successfully.";
    } else {
        $error_msg = "Update failed: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<?php
      include('assets/inc/header.php');
?>

<?php if (!empty($success_msg)): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($success_msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($error_msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<a href="profile.php">
    <button class="btn btn-primary">Go Back</button>
</a>
<?php
      include('assets/inc/footer.php');
?>
