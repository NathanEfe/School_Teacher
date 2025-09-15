<?php include('assets/inc/header.php'); ?>
<h3>Broad Sheet</h3>

<?php
include 'db_connect.php';

// ================= FILTER HANDLING ===================
$class_id   = $_GET['class_id']   ?? '';
$session    = $_GET['session']    ?? '';
$term       = $_GET['term']       ?? '';

$where = [];
if ($class_id != '')   $where[] = "r.class = '$class_id'";
if ($session != '')    $where[] = "r.session = '$session'";
if ($term != '')       $where[] = "r.term = '$term'";

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

// ================= FETCH SUBJECTS ===================
$subjectsRes = $conn->query("SELECT id, subject_name FROM jss2_subjects ORDER BY id");
$subjects = [];
while ($sub = $subjectsRes->fetch_assoc()) {
    $subjects[$sub['id']] = $sub['subject_name'];
}

// ================= FETCH STUDENT RESULTS ===================
$sql = "SELECT r.student_id, s.name, c.class_name, r.subject, r.total
        FROM results r
        JOIN jss2_students_records s ON r.student_id = s.student_id
        JOIN classes c ON r.class = c.class_id
        $whereSQL
        ORDER BY s.name, r.subject";
$res = $conn->query($sql);

// Organize results per student
$students = [];
while ($row = $res->fetch_assoc()) {
    $sid = $row['student_id'];
    if (!isset($students[$sid])) {
        $students[$sid] = [
            'name'       => $row['name'],
            'class_name' => $row['class_name'],
            'scores'     => array_fill_keys(array_keys($subjects), null),
            'grandTotal' => 0,
            'count'      => 0,
            'average'    => 0,
        ];
    }
    $students[$sid]['scores'][$row['subject']] = $row['total'];
    if ($row['total'] !== null) {
        $students[$sid]['grandTotal'] += $row['total'];
        $students[$sid]['count']++;
    }
}

// Calculate averages
foreach ($students as $sid => &$st) {
    $st['average'] = $st['count'] > 0 ? round($st['grandTotal'] / $st['count'], 2) : 0;
}
unset($st);

// ================= ASSIGN POSITIONS ===================
usort($students, function ($a, $b) {
    return $b['grandTotal'] <=> $a['grandTotal']; // Descending order
});

$position = 0;
$prevTotal = null;
$skip = 1;
foreach ($students as $index => &$st) {
    if ($st['grandTotal'] !== $prevTotal) {
        $position = $index + 1;
        $st['position'] = $position;
    } else {
        $st['position'] = $position; // same position for tie
    }
    $prevTotal = $st['grandTotal'];
}
unset($st);

// Re-index by student_id again
$studentsById = [];
foreach ($students as $st) {
    $studentsById[$st['name']] = $st; // keeping name as key to preserve sorting
}
?>

<!-- =============== FILTER FORM ================= -->
<form method="get" class="row g-3 mb-4" id="filterForm">
    <div class="col-md-3">
        <label for="class_id" class="form-label">Class</label>
        <select name="class_id" id="class_id" class="form-select" required>
            <option value="">-- Select Class --</option>
            <?php 
            $classes = $conn->query("SELECT * FROM classes ORDER BY class_id");
            while ($c = $classes->fetch_assoc()): ?>
                <option value="<?= $c['class_id'] ?>" <?= ($c['class_id'] == $class_id ? 'selected' : '') ?>>
                    <?= htmlspecialchars($c['class_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-3">
        <label for="session" class="form-label">Session</label>
        <select name="session" id="session" class="form-select" required>
            <option value="">-- Select Session --</option>
            <?php 
            $sessions = $conn->query("SELECT DISTINCT session FROM results ORDER BY id DESC");
            while ($ss = $sessions->fetch_assoc()): ?>
                <option value="<?= $ss['session'] ?>" <?= ($ss['session'] == $session ? 'selected' : '') ?>>
                    <?= htmlspecialchars($ss['session']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-3">
        <label for="term" class="form-label">Term</label>
        <select name="term" id="term" class="form-select">
            <option value="">---Select Term---</option>
            <?php 
            $terms = $conn->query("SELECT DISTINCT term FROM results ORDER BY id ASC");
            while ($t = $terms->fetch_assoc()): ?>
                <option value="<?= $t['term'] ?>" <?= ($t['term'] == $term ? 'selected' : '') ?>>
                    <?= htmlspecialchars($t['term']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>
</form>

<!-- =============== RESULTS TABLE ================= -->
<?php if ($class_id && $session): ?>
 <div class="card mt-4">
  <div class="card-header bg-primary text-white">
    Broad Sheet
  </div>
  <div class="card-body">
      <?php if (!empty($studentsById)): ?>
          <table class="table table-bordered table-striped table-sm text-center">
              <thead>
                  <tr>
                      <th>Student ID</th>
                      <th>Name</th>
                      <th>Class</th>
                      <?php foreach ($subjects as $sub): ?>
                          <th><?= htmlspecialchars($sub) ?></th>
                      <?php endforeach; ?>
                      <th>Grand Total</th>
                      <th>Average</th>
                      <th>Position</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach ($studentsById as $sid => $st): ?>
                      <tr>
                          <td><?= htmlspecialchars($sid) ?></td>
                          <td><?= htmlspecialchars($st['name']) ?></td>
                          <td><?= htmlspecialchars($st['class_name']) ?></td>
                          <?php foreach ($st['scores'] as $score): ?>
                              <td><?= $score !== null ? htmlspecialchars($score) : '-' ?></td>
                          <?php endforeach; ?>
                          <td><strong><?= $st['grandTotal'] ?></strong></td>
                          <td><strong><?= $st['average'] ?></strong></td>
                          <td><strong><?= $st['position'] ?></strong></td>
                      </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>
      <?php else: ?>
          <p class="text-muted">No results found for the selected filters.</p>
      <?php endif; ?>
  </div>
 </div>
<?php endif; ?>

<?php include('assets/inc/footer.php'); ?>
