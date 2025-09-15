<?php include ('assets/inc/header.php')?>
<?php
include('db_connect.php');

// Get all classes
$classes = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC");

// Defaults
$class_id = $_GET['class_id'] ?? '';
$date     = $_GET['date'] ?? date('Y-m-d');

$records = [];
if (!empty($class_id) && !empty($date)) {
    // Get morning and evening in one query
    $stmt = $conn->prepare("
        SELECT s.student_id, s.name, a.session, a.status
        FROM jss2_students_records s
        LEFT JOIN attendance a 
          ON s.student_id = a.student_id 
         AND a.date = ?
        WHERE s.class_id = ?
        ORDER BY s.student_id ASC
    ");
    $stmt->bind_param("si", $date, $class_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Arrange results into one row per student
    while ($row = $result->fetch_assoc()) {
        $id = $row['student_id'];
        if (!isset($records[$id])) {
            $records[$id] = [
                'student_id' => $row['student_id'],
                'name'       => $row['name'],
                'Morning'    => 'Not Taken',
                'Evening'    => 'Not Taken'
            ];
        }
        if ($row['session'] === 'Morning') {
            $records[$id]['Morning'] = $row['status'];
        }
        if ($row['session'] === 'Evening') {
            $records[$id]['Evening'] = $row['status'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Attendance</title>
</head>
<body>

<div class="container mt-4">
<h3>View Attendance</h3>
  <!-- Filter Form -->
   <div class="card mt-4">
    <div class="card-header bg-info text-white">View Attendance</div>
      <div class="card-body">
        <form method="get" class="row g-3 mb-4 mt-4">
          <div class="col-md-6">
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select" required>
              <option value="">-- Select Class --</option>
              <?php while($row = $classes->fetch_assoc()): ?>
                <option value="<?= $row['class_id'] ?>" <?= ($class_id == $row['class_id'])?'selected':'' ?>>
                  <?= htmlspecialchars($row['class_name']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="form-control" required>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-info w-100 mt-4">Filter</button>
          </div>
        </form>
      </div>
    </div>
  <?php if (!empty($records)): ?>
    <div class="card">
      <div class="card-header bg-primary text-white">  
        <p>Attendance Records for <?= htmlspecialchars(date("F j, Y", strtotime($date))) ?></p>
      </div>
      <div class="card-body">
  <table class="table table-bordered">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Student Name</th>
        <th>Student ID</th>
        <th>Morning Status</th>
        <th>Evening Status</th>
      </tr>
    </thead>
    <tbody>
      <?php $i=1; foreach ($records as $row): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['student_id']) ?></td>
          <td>
            <?php if ($row['Morning'] === 'Present'): ?>
              <span class="badge bg-success text-white">Present</span>
            <?php elseif ($row['Morning'] === 'Absent'): ?>
              <span class="badge bg-danger text-white">Absent</span>
            <?php else: ?>
              <span class="badge bg-secondary text-white">Not Taken</span>
            <?php endif; ?>

          </td>
          <td>
            <?php if ($row['Evening'] === 'Present'): ?>
              <span class="badge bg-success text-white">Present</span>
            <?php elseif ($row['Evening'] === 'Absent'): ?>
              <span class="badge bg-danger text-white">Absent</span>
            <?php else: ?>
              <span class="badge bg-secondary text-white">Not Taken</span>
            <?php endif; ?>


          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  
  <?php elseif (!empty($class_id) && !empty($date)): ?>
    <div class="alert alert-warning">No records found.</div>
  <?php endif; ?>
      </div>
    </div>
</div>

</body>
</html>
<?php include ('assets/inc/footer.php')?>
