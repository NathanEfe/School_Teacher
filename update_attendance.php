<?php
include("db_connect.php");

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    $session = $_POST['session'];
    $attendance = $_POST['attendance']; // array [student_id => status]

    foreach ($attendance as $student_id => $status) {
        $stmt = $conn->prepare("UPDATE attendance 
                                SET status = ? 
                                WHERE student_id = ? AND class_id = ? AND date = ? AND session = ?");
        $stmt->bind_param("sssss", $status, $student_id, $class_id, $date, $session);
        $stmt->execute();
    }

    header("Location: update_attendance.php?msg=updated&class_id=$class_id&date=$date&session=$session");
    exit;
}

// Fetch classes
$classes = $conn->query("SELECT * FROM classes");

// Handle filtering
$class_id = $_GET['class_id'] ?? '';
$date     = $_GET['date'] ?? '';
$session  = $_GET['session'] ?? '';
$students = [];

if ($class_id && $date && $session) {
    $sql = "SELECT s.student_id, s.name, a.status 
            FROM jss2_students_records s
            LEFT JOIN attendance a 
              ON s.student_id = a.student_id 
             AND a.class_id = ? 
             AND a.date = ? 
             AND a.session = ?
            WHERE s.class_id = ?
            ORDER BY s.student_id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $class_id, $date, $session, $class_id);
    $stmt->execute();
    $students = $stmt->get_result();
}
?>

<?php 
session_start();
include('assets/inc/header.php');

if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}
 ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Attendance</title>
</head>
<body class="bg-light">
<div class="container mt-4">
  <h3 class="mb-4">Update Attendance</h3>

  <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
    <div class="alert alert-success">Attendance updated successfully!</div>
  <?php endif; ?>

  <!-- Filter Form -->
   <div class="card">
    <div class="card-header bg-info text-white">Update Attendance</div>
      <div class="card-body">
        <form method="get" class="row g-3 mb-4">
          <div class="col-md-4">
            <label class="form-label">Select Class</label>
            <select name="class_id" class="form-select" required>
              <option value="">-- Select Class --</option>
              <?php while($row = $classes->fetch_assoc()): ?>
                <option value="<?= $row['class_id'] ?>" <?= ($class_id == $row['class_id'])?'selected':'' ?>>
                  <?= $row['class_name'] ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($date ?: date('Y-m-d')) ?>" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Session</label>
            <select name="session" class="form-select" required>
              <option value="">-- Select Session --</option>
              <option value="Morning" <?= ($session === 'Morning')?'selected':'' ?>>Morning</option>
              <option value="Evening" <?= ($session === 'Evening')?'selected':'' ?>>Evening</option>
            </select>
          </div>
          <div class="col-md-2 d-flex mt-4 align-items-end">
            <button type="submit" class="btn btn-info w-100">Load Attendance</button>
          </div>
        </form>
      </div>
    </div>
  <!-- Update Form -->
  <?php if ($students && $students->num_rows > 0): ?>
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
      <strong>Update Attendance Records for <?= htmlspecialchars(date("F j, Y", strtotime($date))) ?> (<?= htmlspecialchars($session) ?>)</strong>
    </div>
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="class_id" value="<?= $class_id ?>">
        <input type="hidden" name="date" value="<?= $date ?>">
        <input type="hidden" name="session" value="<?= $session ?>">

        <table class="table table-bordered">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Student Name</th>
              <th>Student ID</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
           <?php $i=1; while($row = $students->fetch_assoc()): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['student_id']) ?></td>
            <td>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" 
                          type="radio" 
                          name="attendance[<?= $row['student_id']; ?>]" 
                          id="present_<?= $row['student_id']; ?>" 
                          value="Present"
                          <?= ($row['status'] === "Present") ? "checked" : "" ?>>
                    <label class="form-check-label" for="present_<?= $row['student_id']; ?>">Present</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" 
                          type="radio" 
                          name="attendance[<?= $row['student_id']; ?>]" 
                          id="absent_<?= $row['student_id']; ?>" 
                          value="Absent"
                          <?= ($row['status'] === "Absent") ? "checked" : "" ?>>
                    <label class="form-check-label" for="absent_<?= $row['student_id']; ?>">Absent</label>
                </div>

                <!-- <div class="form-check form-check-inline">
                    <input class="form-check-input" 
                          type="radio" 
                          name="attendance[<?= $row['student_id']; ?>]" 
                          id="late_<?= $row['student_id']; ?>" 
                          value="Late"
                          <?= ($row['status'] === "Late") ? "checked" : "" ?>>
                    <label class="form-check-label" for="late_<?= $row['student_id']; ?>">Late</label>
                </div> -->
            </td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>

        <div class="text-end">
          <button type="submit" class="btn btn-success">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
  <?php elseif($class_id && $date && $session): ?>
    <div class="alert alert-warning">
      No attendance records found for this class, date, and session. 
      Please, <a href="take_attendance.php" class="alert-link">Take Attendance</a> first.
    </div>
  <?php endif; ?>
</div>

</body>
</html>

<?php include('assets/inc/footer.php'); ?>
