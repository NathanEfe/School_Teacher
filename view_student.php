<?php
session_start();
if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}

include('assets/inc/header.php');
include 'db_connect.php'; // DB connection

// ================== VALIDATE INPUT ==================
$student_id = $_GET['id'] ?? '';
if (!$student_id) {
    echo "<div class='alert alert-danger'>No student selected.</div>";
    include('assets/inc/footer.php');
    exit;
}

// ================== FETCH STUDENT INFO ==================
$stmt = $conn->prepare("SELECT s.student_id, s.name, c.class_name, s.date_of_birth, s.parent_name, s.mobile_number, s.address, s.profile_picture
                        FROM jss2_students_records s
                        JOIN classes c ON s.class_id = c.class_id
                        WHERE s.student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    echo "<div class='alert alert-danger'>Student not found.</div>";
    include('assets/inc/footer.php');
    exit;
}
?>

<h3 class="h3 mb-4">Student Profile</h3>
<?php
 $dob = new DateTime($student['date_of_birth']); //format dob 
        $today = new DateTime();
         // Calculate age
        $age = $today->diff($dob)->y;
?>
<div class="card mb-4 shadow-sm">
  <div class="card-body">
<img src="<?= !empty($student['profile_picture']) ? htmlspecialchars($student['profile_picture']) : './assets/images/user/avatar-2.png' ?>"
             alt="Profile Picture"
             class="rounded-circle mb-2"
             width="100"
             height="100"
             style="border-radius:50%;">
    <p class="mt-4"><strong>Student ID:</strong> <?= htmlspecialchars($student['student_id']) ?></p>
    <p class="mt-4"><strong>Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
    <p class="mt-4"><strong>Class:</strong> <?= htmlspecialchars($student['class_name']) ?></p>
    <p class="mt-4"><strong>Date of Birth:</strong> <?= date('d-m-Y', strtotime($student['date_of_birth'])) ?></p>
    <p class="mt-4"><strong>Age:</strong> <?php echo $age; ?></p>
    <p class="mt-4"><strong>Parent/Guardian Name:</strong> <?= htmlspecialchars($student['parent_name']) ?></p>
    <p class="mt-4"><strong>Parent/Guardian Phone Number:</strong> <?= htmlspecialchars($student['mobile_number']) ?></p>
    <p class="mt-4"><strong>House Address:</strong> <?= htmlspecialchars($student['address']) ?></p>
    <a href="students_overview.php" class="btn btn-primary mt-4">Go Back</a>
    <a href="edit_student.php?id=<?=urldecode($student['student_id'])?>" class='btn btn-warning mt-4'>Edit Details</a>
  </div>
</div>



<?php
include('assets/inc/footer.php');
?>

