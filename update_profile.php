<!-- Update Profile Section -->

<?php
error_reporting(0);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login/login.php");
    exit();
}

include('db_connect.php');


$success_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];

    $full_name   = trim($_POST['full_name']);
    $email       = trim($_POST['email']);
    $phone       = trim($_POST['phone']);
    $address     = trim($_POST['address']);
    

    // File upload
    $profile_pic_path = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/";
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $new_name = "profile_" . uniqid() . "." . $ext;
        $target_file = $upload_dir . $new_name;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_pic_path = $target_file;
        } else {
            $error_msg = "Failed to upload profile picture.";
        }
    }


    $query = "UPDATE teacher_register SET full_name = ?, email = ?, phone_number = ?, address = ?" . 
             ($profile_pic_path ? ", profile_picture = ?" : "") . 
             " WHERE user_id = ?";


    if ($profile_pic_path) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $full_name, $email, $phone, $address, $profile_pic_path, $user_id);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $full_name, $email, $phone, $address, $user_id);
    }

    if ($stmt->execute()) {
        $success_msg = "Profile updated successfully.";
    } else {
        $error_msg = "Update failed: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>


 <?php
// display profile picture
$conn = new mysqli("localhost", "root", "", "school");

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, email, profile_picture FROM teacher_register WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
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
