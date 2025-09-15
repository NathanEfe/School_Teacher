<?php
error_reporting(0);
session_start();
require 'db_connect.php';

if (!isset($_SESSION['staff_id'])) {
    die("Unauthorized access.");
}


$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_id = $_SESSION['staff_id'];
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "<div class='alert alert-danger'>All fields are required.</div>";
    } elseif ($new_password !== $confirm_password) {
        $message = "<div class='alert alert-danger'>New passwords do not match.</div>";
    } else {
        $sql = "SELECT password_hash FROM teacher_register WHERE staff_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $staff_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($hashed_password);
            $stmt->fetch();

            if (password_verify($current_password, $hashed_password)) {
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $update_sql = "UPDATE teacher_register SET password_hash = ? WHERE staff_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ss", $new_hashed_password, $staff_id);

                if ($update_stmt->execute()) {
                    $message = "<div class='alert alert-success'>Password updated successfully.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Error updating password.</div>";
                }

                $update_stmt->close();
            } else {
                $message = "<div class='alert alert-danger'>Incorrect current password.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>User not found.</div>";
        }

        $stmt->close();
    }
}
?>




<?php
      include('assets/inc/header.php');
?>

<?php if (!empty($message)) echo $message; ?>


<a href="profile.php">
    <button class="btn btn-primary">Go Back</button>
</a>
<?php
      include('assets/inc/footer.php');
?>