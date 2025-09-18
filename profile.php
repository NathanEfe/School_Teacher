<?php 
session_start();
include('assets/inc/header.php');

if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}

?>


<?php

include('db_connect.php');

$staff_id = $_SESSION['staff_id'];

$stmt = $conn->prepare("SELECT * FROM teacher_register WHERE staff_id = ?");
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>


<?php
// echo "Logged-in User ID: " . $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>My Profile</title>
</head>
<body>
      <h1>My Profile</h1>
      <div class="card mb-4 shadow-sm w-100">
  <div class="card-body">
    <!-- Profile Picture -->
    <img src="<?= !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : './assets/images/user/avatar-2.jpg' ?>" alt="Profile Picture" class="rounded-circle mb-3" width="100" height="100" style="border-radius: 50%;">

    <!-- Full Name -->
    <h5 class="card-title mb-1">
      Staff ID : <?php echo $_SESSION['staff_id']; ?>
    </h5>

    <!-- User Role -->
    <p class="text-muted mb-2">Role: <strong><?= htmlspecialchars($user['job_title'] ?? '') ?></strong></p>

    <!-- User ID -->
    <p class="mb-1"><strong>ID:</strong><?php echo $_SESSION['staff_id']; ?></p>

    <!-- Department -->
    <p class="mb-1"><strong>Class:</strong> <?= htmlspecialchars($user['department'] ?? '') ?></p>

    <!-- Status -->
    <p class="mb-0">
      <strong>Status:</strong>
      <span class="badge bg-success" id="settings">Active</span>
    </p>
  </div>
</div>

<div class="card mb-4 shadow-sm">
  <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
    <strong>Edit / Update Profile</strong>
    <button class="btn btn-warning btn-sm" type="button" id="editBtn" onclick="toggleEditMode()">Edit</button>
  </div>

  <div class="card-body">
  <form id="profileForm" action="update_profile.php" method="POST" enctype="multipart/form-data">
      <!-- Profile Picture Upload -->
      <div class="text-center mb-4">
        <img src="<?= !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : './assets/images/user/avatar-2.jpg' ?>" alt="Profile Picture" class="rounded-circle mb-2" id="profilePic" width="100" height="100" style="border-radius: 50%;">
        <div>
          <h6 class="text-start mt-4">Upload Profile Picture</h6>
          <input type="file" class="form-control form-control-sm w-auto d-inline-block" id="profilePicInput" disabled name="profile_picture"  accept="image/jpeg, image/png, .gif, image/jpg">
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="editFullName" class="form-label">Full Name</label>
          <input type="text" class="form-control" name="full_name" id="editFullName" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" disabled required>
        </div>
        <div class="col-md-6">
          <label for="editEmail" class="form-label">Email</label>
          <input type="email" class="form-control" id="editEmail" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled required readonly>
        </div>
      </div>

      <div class="mb-3">
        <label for="editPhone" class="form-label">Phone Number</label>
        <input type="tel" class="form-control" name="phone" id="editPhone" value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>" disabled required>
      </div>

      <div class="mb-3">
        <label for="editAddress" class="form-label">Address</label>
        <textarea class="form-control" name="address" id="editAddress" rows="2" disabled required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
      </div>
      <?php if (!empty($success_msg)): ?>
  <div class="alert alert-success"><?= $success_msg ?></div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
  <div class="alert alert-danger"><?= $error_msg ?></div>
<?php endif; ?>


      <div class="text-end">
        <button type="submit" class="btn btn-info" id="saveBtn" disabled>Save Changes</button>
      </div>
    </form>
  </div>
</div>

<div class="card mb-4 shadow-sm">
  <div class="card-header bg-primary text-white">
    <strong>Personal Information</strong>
  </div>
  <div class="card-body">
    <form action="update_personal_information.php" method="POST">
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="firstName" class="form-label">Full Name</label>
          <input type="text" class="form-control" name="full_name" id="editFullName" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" readonly>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="dob" class="form-label">Date of Birth</label>
          <input type="date" class="form-control" id="dob" name="date_of_birth" value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>">
        </div>
        <div class="col-md-6">
  <label for="gender" class="form-label">Gender</label>
  <select class="form-select" id="gender" name="gender">
    <option value="Male" <?= ($user['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
    <option value="Female" <?= ($user['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
  </select>
</div>

      </div>

     <div class="col-md-6 mb-4">
  <label for="bloodGroup" class="form-label">Blood Group</label>
  <select class="form-select" id="bloodGroup" name="blood_group">
    <?php
      $blood_options = ["O+", "A+", "B+", "AB+", "O-", "A-", "B-", "AB-"];
      $selected_blood = $user['blood_group'] ?? '';
      foreach ($blood_options as $group) {
          $selected = ($selected_blood === $group) ? 'selected' : '';
          echo "<option value=\"$group\" $selected>$group</option>";
      }
    ?>
  </select>
</div>
      <div class="text-end">
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<div class="card mb-4 shadow-sm">
  <div class="card-header bg-secondary text-white">
    <strong>Professional Information</strong>
  </div>
  <div class="card-body">
    <form action="update_professional_information.php" method="post">
  <div class="row mb-3">
    <div class="col-md-6">
      <label for="jobTitle" class="form-label">Job Title</label>
      <input type="text" class="form-control" id="jobTitle" name="job_title" value="<?= htmlspecialchars($user['job_title'] ?? '') ?>" >
    </div>
    <div class="col-md-6">
      <label for="department" class="form-label">Class(es)</label>
      <input type="text" class="form-control" id="department" name="department" value="<?= htmlspecialchars($user['department'] ?? '') ?>">
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-md-6">
      <label for="licenseNo" class="form-label">License/Registration Number</label>
      <input type="text" class="form-control" id="licenseNo" name="license_number" value="<?= htmlspecialchars($user['license_number'] ?? '') ?>" >
    </div>
    <div class="col-md-6">
      <label for="experience" class="form-label">Years of Experience</label>
      <input type="number" class="form-control" id="experience" name="years_of_experience" value="<?= htmlspecialchars($user['years_of_experience'] ?? '') ?>" >
    </div>
  </div>

  <div class="mb-3">
    <label for="qualification" class="form-label">Qualification</label>
    <input type="text" class="form-control" id="qualification" name="qualification" value="<?= htmlspecialchars($user['qualification'] ?? '') ?>" >
  </div>

  <div class="text-end">
    <button type="submit" class="btn btn-secondary">Save Changes</button>
  </div>
</form>

  </div>
</div>

<div class="card mb-4 shadow-sm" id="change_password">
  <div class="card-header bg-warning text-dark">
    <strong>Change Password</strong>
  </div>
  <div class="card-body">
    <form method="POST" action="change_password.php">
      <div class="mb-3">
        <label for="currentPassword" class="form-label">Current Password</label>
        <div class="input-group">
          <input type="password" class="form-control" id="currentPassword" name="current_password">
          <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('currentPassword', this)">
            Show
          </button>
        </div>
      </div>

      <div class="mb-3">
        <label for="newPassword" class="form-label">New Password</label>
        <div class="input-group">
          <input type="password" class="form-control" id="newPassword" oninput="checkStrength(this.value)" name="new_password" >
          <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newPassword', this)">
            Show
          </button>
        </div>
        <!-- Password strength meter -->
        <div class="progress mt-2" style="height: 5px;">
          <div id="strengthBar" class="progress-bar bg-danger" style="width: 0%;"></div>
        </div>
        <small id="strengthText" class="text-muted"></small>
      </div>

      <div class="mb-3">
        <label for="confirmPassword" class="form-label">Confirm New Password</label>
        <div class="input-group">
          <input type="password" class="form-control" id="confirmPassword" name="confirm_password">
          <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmPassword', this)">
            Show
          </button>
        </div>
      </div>

      <div class="text-end">
        <button type="submit" class="btn btn-warning">Update Password</button>
      </div>
    </form>
  </div>
</div>




<script>
  function toggleEditMode() {
  const form = document.getElementById("profileForm");
  const inputs = form.querySelectorAll("input, textarea, button");

  inputs.forEach(input => {
    if (input.id !== "editBtn") {
      input.disabled = !input.disabled;
    }
  });
}
</script>

<script>
function togglePassword(fieldId, btn) {
  const input = document.getElementById(fieldId);
  if (input.type === "password") {
    input.type = "text";
    btn.textContent = "Hide";
  } else {
    input.type = "password";
    btn.textContent = "Show";
  }
}

function checkStrength(password) {
  const strengthBar = document.getElementById("strengthBar");
  const strengthText = document.getElementById("strengthText");

  let strength = 0;
  if (password.length > 6) strength++;
  if (password.match(/[A-Z]/)) strength++;
  if (password.match(/[0-9]/)) strength++;
  if (password.match(/[^a-zA-Z0-9]/)) strength++;

  const percentage = (strength / 4) * 100;
  strengthBar.style.width = percentage + "%";

  if (percentage < 50) {
    strengthBar.className = "progress-bar bg-danger";
    strengthText.textContent = "Weak";
  } else if (percentage < 75) {
    strengthBar.className = "progress-bar bg-warning";
    strengthText.textContent = "Moderate";
  } else {
    strengthBar.className = "progress-bar bg-success";
    strengthText.textContent = "Strong";
  }
}
</script>


</body>
</html>


<?php

      include('assets/inc/footer.php'); 
?>