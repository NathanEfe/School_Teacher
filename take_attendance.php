<?php 
session_start();
include('assets/inc/header.php');

if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}
?>
<h3>Take Attendance</h3>
<span><p class="text-start mt-4">Date: <?= date('l, F j, Y') ?></p></span>

<?php
include('db_connect.php');

// Fetch all classes
$classes = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");

// Get selected class & date (if any)
$class_id = $_GET['class_id'] ?? '';
$date = $_GET['date'] ?? date("Y-m-d");

// Fetch all classes
$classes = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");

// Get selected class & date (if any)
$class_id = $_GET['class_id'] ?? '';
$date     = $_GET['date'] ?? date("Y-m-d");
$session = $_GET['session'] ?? '';
// Default value for attendance_taken
$attendance_taken = false;
$students = [];

if (!empty($class_id) && !empty($session)) {
    $stmt_check = $conn->prepare("SELECT COUNT(*) as cnt 
                                  FROM attendance 
                                  WHERE class_id = ? AND date = ? AND session = ?");
    $stmt_check->bind_param("iss", $class_id, $date, $session);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result()->fetch_assoc();
    if ($result_check['cnt'] > 0) {
        $attendance_taken = true;
    }

    // Fetch students
    $stmt = $conn->prepare("SELECT student_id, name 
                            FROM jss2_students_records 
                            WHERE class_id = ? 
                            ORDER BY student_id ASC");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $students = $stmt->get_result();
}

?>

<div class="container mt-5">

  <!-- Alerts -->
<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Attendance saved successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

<?php elseif ($_GET['msg'] === 'already_taken'): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        Attendance has <strong>already been taken</strong> for 
        <?= htmlspecialchars($_GET['session'] ?? '') ?> Session on 
        <?= htmlspecialchars(date("F j, Y", strtotime($_GET['date'] ?? ''))) ?>.  
        If you need to make changes, please use the 
        <a href="update_attendance.php" class="alert-link">Update Attendance</a> page.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <?php elseif ($_GET['msg'] === 'error'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Something went wrong while saving attendance. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>

 <?php if (!empty($class_id) && $attendance_taken): ?>
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
        Attendance has <strong>already been taken</strong> for 
       <b class="alert-link"> <?= htmlspecialchars($_GET['session'] ?? '') ?> Session </b> on 
        <b class="alert-link"> <?= htmlspecialchars(date("F j, Y", strtotime($_GET['date'] ?? ''))) ?>.   </b>
        If you need to make changes, please use the 
        <a href="update_attendance.php" class="alert-link">Update Attendance</a> page.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card">
  <div class="card-header bg-info text-white">Take Attendance</div>
  <div class="card-body">
  <!-- Filter Form -->
  <form method="get" class="row g-3 mb-4">
    <div class="col-md-6">
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
  <input type="date" name="date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" class="form-control" required>
</div>
          <div class="col-md-2">
            <label class="form-label">Session</label>
            <select name="session" class="form-select" required>
              <option value="">-- Select Session --</option>
              <option value="Morning" <?= (($_GET['session'] ?? '') === 'Morning')?'selected':'' ?>>Morning</option>
              <option value="Evening" <?= (($_GET['session'] ?? '') === 'Evening')?'selected':'' ?>>Evening</option>
            </select>
          </div>


    <div class="col-md-2 d-flex mt-4 align-items-end">
      <button type="submit" class="btn btn-info w-100">Filter</button>
    </div>
  </form>

        </div>
</div>
  <?php if (!empty($class_id) && !($attendance_taken)): ?>
  <form action="save_attendance.php" method="post">
    <input type="hidden" name="class_id" value="<?= $class_id; ?>">
    <input type="hidden" name="date" value="<?= $date; ?>">
    <input type="hidden" name="session" value="<?= $session; ?>">


    <div class="card mb-4 shadow-sm mt-4">
      <div class="card-header bg-primary text-white">
    <strong>Attendance List</strong> For <strong> <?= htmlspecialchars(date("F j, Y", strtotime($date))) ?> <?= htmlspecialchars($session) ?> Session</strong>
      </div>

      <div class="card-body">
          <table class="table table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Student Name</th>
          <th>Student ID</th>
          <th class="text-center">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($students && $students->num_rows > 0): ?>
          <?php $i=1; while($row = $students->fetch_assoc()): ?>
            <tr>
              <td><?= $i++; ?></td>
              <td><?= htmlspecialchars($row['name']); ?></td>
              <td><?= htmlspecialchars($row['student_id']); ?></td>
              <td>
                  <!-- Hidden inputs should be keyed by student_id -->
                  <input type="hidden" name="student_name[<?= $row['student_id']; ?>]" value="<?= htmlspecialchars($row['name']); ?>">
                  <input type="hidden" name="student_id[<?= $row['student_id']; ?>]" value="<?= htmlspecialchars($row['student_id']); ?>">
                <div class="form-check">
                    <input class="form-check-input" type="radio" 
                          name="attendance[<?= $row['student_id']; ?>]" 
                          id="present_<?= $row['student_id']; ?>" 
                          value="Present" required>
                    <label class="form-check-label" for="present_<?= $row['student_id']; ?>">Present</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" 
                          name="attendance[<?= $row['student_id']; ?>]" 
                          id="absent_<?= $row['student_id']; ?>" 
                          value="Absent">
                    <label class="form-check-label" for="absent_<?= $row['student_id']; ?>">Absent</label>
                </div>
                <!-- <div class="form-check">
                    <input class="form-check-input" type="radio" 
                          name="attendance[<?= $row['student_id']; ?>]" 
                          id="late_<?= $row['student_id']; ?>" 
                          value="Late">
                    <label class="form-check-label" for="late_<?= $row['student_id']; ?>">Late</label>
                </div> -->
              </td>

            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="3" class="text-center text-muted">No students found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
      </div>
    </div>
  

    <button type="submit" class="btn btn-success w-100">Save Attendance</button>
  </form>
  <?php endif; ?>

</div>

<?php include('assets/inc/footer.php'); ?>
