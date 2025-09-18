<?php
session_start();
include('assets/inc/header.php');

if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}
include 'db_connect.php';

// ================== VALIDATE INPUT ==================
$student_id = $_GET['id'] ?? '';
if (empty($student_id)) {
    echo "<div class='alert alert-danger'>No student selected for editing.</div>";
    include('assets/inc/footer.php');
    exit;
}

// ================== FETCH STUDENT INFO ==================
$stmt = $conn->prepare("
    SELECT s.*, c.class_name
    FROM jss2_students_records s
    LEFT JOIN classes c ON s.class_id = c.class_id
    WHERE s.student_id = ?
");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    echo "<div class='alert alert-danger'>Student not found.</div>";
    include('assets/inc/footer.php');
    exit;
}

// ================== UPDATE HANDLER ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name'] ?? $student['name']);
    $class_id     = $student['class_id']; // keep current class
    $dob          = trim($_POST['date_of_birth']);
    $parent_name  = trim($_POST['parent_name']);
    $mobile       = trim($_POST['mobile_number']);
    $address      = trim($_POST['address']);

    // Handle profile picture
    $profile_picture = $student['profile_picture']; 
    if (!empty($_FILES['profile_picture']['name'])) {
        $uploadDir = "uploads/students/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES['profile_picture']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
            $profile_picture = $targetFile;
        }
    }

    $sql = "UPDATE jss2_students_records
            SET name = ?, class_id = ?, date_of_birth = ?, parent_name = ?, mobile_number = ?, address = ?, profile_picture = ?, updated_on = NOW()
            WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $name, $class_id, $dob, $parent_name, $mobile, $address, $profile_picture, $student_id);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Student details updated successfully. 
                <a href="view_student.php?id=' . urlencode($student['student_id']) . '" class="alert-link">Go Back</a>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';

        // Refresh data
        $student['name'] = $name;
        $student['date_of_birth'] = $dob;
        $student['parent_name'] = $parent_name;
        $student['mobile_number'] = $mobile;
        $student['address'] = $address;
        $student['profile_picture'] = $profile_picture;
        // keep class_name from initial join
    } else {
        echo "<div class='alert alert-danger'>Error updating record: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

// ================== FETCH CLASSES FOR DROPDOWN ==================
$classes = [];
$res = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC");
while ($row = $res->fetch_assoc()) {
    $classes[] = $row;
}
?>

<h3 class="h3 mb-4">Edit Student</h3>

<div class="card mb-4 shadow-sm">
  <div class="card-body">
    <form method="post" enctype="multipart/form-data">
     <div class="mb-3">
        <label class="form-label">Profile Picture</label><br>
        <img src="<?= !empty($student['profile_picture']) ? htmlspecialchars($student['profile_picture']) : './assets/images/user/avatar-2.png' ?>"
             alt="Profile Picture"
             class="rounded-circle mb-2"
             width="100"
             height="100"
             style="border-radius:50%;" >
        <input type="file" name="profile_picture" class="form-control mt-2" accept="image/jpeg, image/png, .gif, image/jpg">
      </div>

      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" required readonly>
      </div>

      <div class="mb-3">
          <label class="form-label">Class</label>
          <input type="text" class="form-control" 
                value="<?= htmlspecialchars($student['class_name']) ?>" readonly>
      </div>


      <div class="mb-3">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($student['date_of_birth']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Parent/Guardian Name</label>
        <input type="text" name="parent_name" class="form-control" value="<?= htmlspecialchars($student['parent_name']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Parent/Guardian Phone Number</label>
        <input type="text" name="mobile_number" class="form-control" value="<?= htmlspecialchars($student['mobile_number']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">House Address</label>
        <textarea name="address" class="form-control"><?= htmlspecialchars($student['address']) ?></textarea>
      </div>

     

      <button type="submit" class="btn btn-success">Update Student</button>
      <a href="view_student.php?id=<?= urlencode($student['student_id']) ?>" class="btn btn-secondary">Go Back</a>
    </form>
  </div>
</div>

<?php include('assets/inc/footer.php'); ?>
