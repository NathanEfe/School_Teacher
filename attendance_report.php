<?php
include("db_connect.php");



// Fetch classes
$classes = $conn->query("SELECT * FROM classes");

// Handle filtering
$class_id = $_GET['class_id'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$students = [];
$dates = [];
if ($class_id && $from_date && $to_date) {
    // Get students of the class
    $students_sql = "SELECT student_id, name FROM jss2_students_records ORDER BY student_id ASC";
    $students = $conn->query($students_sql)->fetch_all(MYSQLI_ASSOC);

    // Get all distinct dates in range
    $dates_sql = "SELECT DISTINCT date FROM attendance 
                  WHERE class_id = ? AND date BETWEEN ? AND ? 
                  ORDER BY date ASC";
    $stmt = $conn->prepare($dates_sql);
    $stmt->bind_param("sss", $class_id, $from_date, $to_date);
    $stmt->execute();
    $dates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch attendance in range
    $att_sql = "SELECT student_id, date, status 
                FROM attendance 
                WHERE class_id = ? AND date BETWEEN ? AND ?";
    $stmt = $conn->prepare($att_sql);
    $stmt->bind_param("sss", $class_id, $from_date, $to_date);
    $stmt->execute();
    $attendance_raw = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Re-arrange into array [student_id][date] = status
    $attendance = [];
    foreach ($attendance_raw as $row) {
        $attendance[$row['student_id']][$row['date']] = $row['status'];
    }
}
?>

<?php 
session_start();
include('assets/inc/header.php');
include('db_connect.php'); 

if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}
?>


<div class="container mt-4">
  <h3 class="mb-4">Attendance Report</h3>

  <!-- Filter Form -->
   <div class="card">
    <div class="card-header bg-info text-white">Attendance Report</div>
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
          <div class="col-md-3">
            <label class="form-label">From</label>
            <input type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>" max="<?= date('Y-m-d'); ?>" class="form-control" required>
          </div>
          <div class="col-md-2 d-flex mt-4 align-items-end">
            <button type="submit" class="btn btn-info w-100">Generate</button>
          </div>
        </form>
    </div>
   </div>


  <?php if ($students && $dates): ?>
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
      <strong>Attendance Report (<?= htmlspecialchars(date("F j, Y", strtotime($from_date))) ?> 
        - <?= htmlspecialchars(date("F j, Y", strtotime($to_date))) ?>)</strong>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-bordered table-sm" id="my-table">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Student ID</th>
            <?php foreach ($dates as $d): ?>
              <th><?= htmlspecialchars(date("M j", strtotime($d['date']))) ?></th>
            <?php endforeach; ?>
            <th>Number of Times Present</th>
            <th>Number of Times Absent</th>
          </tr>
        </thead>
        <tbody>
          <?php $i=1; foreach ($students as $stu): ?>
          <?php 
            $present_count = 0; 
            $absent_count = 0;
            $row_html = "";
            foreach ($dates as $d) {
                $dt = $d['date'];
                if (isset($attendance[$stu['student_id']][$dt])) {
                    $status = $attendance[$stu['student_id']][$dt];
                    if ($status == "Present") $present_count++;
                    if  ($status == "Absent") $absent_count++;
                } else {
                    $status = "Not Taken";
                }
                $badge_class = ($status=="Present"?"success":($status=="Absent"?"danger":($status=="Late"?"warning":"secondary")));
                $row_html .= "<td><span class='badge bg-$badge_class text-white'>$status</span></td>";
            }
          ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($stu['name']) ?></td>
            <td><?= htmlspecialchars($stu['student_id']) ?></td>
            <?= $row_html ?>
            <td><strong><?= $present_count ?></strong></td>
            <td><strong><?= $absent_count ?></strong></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
                <button class='btn btn-success mt-4 mb-4' onclick="exportToExcel('my-table', 'Attendance Report')">Export to Excel</button>
    </div>
  </div>
  <?php elseif($class_id && $from_date && $to_date): ?>
    <div class="alert alert-warning">No attendance records found in this range.</div>
  <?php endif; ?>


</div>
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script>
  function exportToExcel(tableID, filename = '') {
  const table = document.getElementById(tableID);
  if (!table) {
    console.error(`Table with ID '${tableID}' not found.`);
    return;
  }

  // Convert the HTML table to a workbook object
  const workbook = XLSX.utils.table_to_book(table);

  // Write the workbook to an XLSX file and trigger the download
  XLSX.writeFile(workbook, `${filename}.xlsx`);
}
</script>
<?php include('assets/inc/footer.php'); ?>
