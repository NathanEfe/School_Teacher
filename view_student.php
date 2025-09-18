<?php
session_start();
include('assets/inc/header.php');

if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}
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
// ================== FETCH RESULTS ==================
$sql = "SELECT sub.subject_name, r.term, r.session,
               r.first_ca, r.second_ca, r.exam, r.total, r.created_at
        FROM results r
        JOIN jss2_subjects sub ON r.subject = sub.id
        WHERE r.student_id = ?
        ORDER BY r.session DESC, r.term ASC, sub.subject_name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$res = $stmt->get_result();
?>


<?php
// ================== FETCH RESULTS ==================
$sql = "SELECT sub.subject_name, r.term, r.session,
               r.first_ca, r.second_ca, r.exam, r.total, r.created_at
        FROM results r
        JOIN jss2_subjects sub ON r.subject = sub.id
        WHERE r.student_id = ?
        ORDER BY r.session DESC, r.term ASC, sub.subject_name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$res = $stmt->get_result();
?>


<?php if ($res->num_rows > 0): ?>

<div class="card">
<div class="card-header bg-primary text-white">Academic Results</div>
<div class="card-body">
<div class="table-responsive">
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Subject</th>
        <th>Term</th>
        <th>Session</th>
        <th>1st CA</th>
        <th>2nd CA</th>
        <th>Exam</th>
        <th>Total</th>
        <th>Date Added</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['subject_name']) ?></td>
          <td><?= htmlspecialchars($row['term']) ?></td>
          <td><?= htmlspecialchars($row['session']) ?></td>
          <td><?= htmlspecialchars($row['first_ca']) ?></td>
          <td><?= htmlspecialchars($row['second_ca']) ?></td>
          <td><?= htmlspecialchars($row['exam']) ?></td>
          <td><strong><?= htmlspecialchars($row['total']) ?></strong></td>
          <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
  <div class="alert alert-info">No results found for this student.</div>
<?php endif; ?>
    </div>
</div>


<?php
$stmt->close();
include('assets/inc/footer.php');
?>

