<?php
include('assets/inc/header.php');
?>
<h3 class="mb-4">View Resources</h3>

<?php
include 'db_connect.php'; // db connection

// ================= FILTER HANDLING ===================
$class_id   = $_GET['class_id']   ?? '';
$subject_id = $_GET['subject_id'] ?? '';
$session    = $_GET['session']    ?? '';

// Build WHERE condition dynamically
$where = [];
if ($class_id != '')   $where[] = "r.class_id = '$class_id'";
if ($subject_id != '') $where[] = "r.subject_id = '$subject_id'";
if ($session != '')    $where[] = "r.session = '$session'";

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

// ================= FETCH RESULTS FOR DISPLAY ===================
$sql = "SELECT r.id, r.title, r.description, r.type, r.url, r.file_path, 
               r.session, r.created_at,
               c.class_name, s.subject_name
        FROM resources r
        LEFT JOIN classes c ON r.class_id = c.class_id
        LEFT JOIN jss2_subjects s ON r.subject_id = s.id
        $whereSQL
        ORDER BY r.created_at DESC";
$res = $conn->query($sql);

// Dropdown Data
$classes  = $conn->query("SELECT * FROM classes ORDER BY class_id");
$subjects = $conn->query("SELECT * FROM jss2_subjects ORDER BY id");
$sessions = $conn->query("SELECT DISTINCT session FROM resources ORDER BY session DESC");
?>

<!-- =============== FILTER FORM ================= -->
<form method="get" class="row mb-3" id="filterForm">
    <div class="col-md-3 mb-4">
        <label>Class</label>
        <select name="class_id" id="class_id" class="form-control">
            <option value="">--Select Class--</option>
            <?php while ($c = $classes->fetch_assoc()): ?>
                <option value="<?= $c['class_id'] ?>" <?= ($c['class_id'] == $class_id ? 'selected' : '') ?>>
                    <?= htmlspecialchars($c['class_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="col-md-3 mb-4">
        <label>Subject</label>
        <select name="subject_id" class="form-control">
            <option value="">--Select Subject--</option>
            <?php while ($s = $subjects->fetch_assoc()): ?>
                <option value="<?= $s['id'] ?>" <?= ($s['id'] == $subject_id ? 'selected' : '') ?>>
                    <?= htmlspecialchars($s['subject_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="col-md-3 mb-4">
        <label>Session</label>
        <select name="session" class="form-control">
            <option value="">--Select Session--</option>
            <?php while ($ss = $sessions->fetch_assoc()): ?>
                <option value="<?= $ss['session'] ?>" <?= ($ss['session'] == $session ? 'selected' : '') ?>>
                    <?= htmlspecialchars($ss['session']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="col-md-12 mt-3 mb-4">
        <button type="submit" class="btn btn-primary">Filter</button>
    </div>
</form>

<!-- =============== RESOURCES TABLE ================= -->

<?php if ($class_id || $subject_id || $session): ?>
<div class="container-fluid mt-4 h-100">
  <div class="card shadow-sm mt-4 mb-4">
    <div class="card-header bg-primary text-white">
      <strong>View Resources</strong>
    </div>
    <div class="card-body">
      <?php if ($res && $res->num_rows > 0): ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Description</th>
                <th>Type</th>
                <th>Resource</th>
                <th>Class</th>
                <th>Subject</th>
                <th>Session</th>
                <th>Uploaded At</th>
              </tr>
            </thead>
            <tbody>
              <?php $i=1; while ($row = $res->fetch_assoc()): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= htmlspecialchars($row['title']) ?></td>
                  <td><?= htmlspecialchars($row['description']) ?></td>
                  <td><?= ucfirst($row['type']) ?></td>
                  <td>
                    <?php if ($row['type'] === 'file' && $row['file_path']): ?>
                      <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="btn btn-sm btn-success">Download</a>
                    <?php elseif ($row['url']): ?>
                      <a href="<?= htmlspecialchars($row['url']) ?>" target="_blank" class="btn btn-sm btn-info">View</a>
                    <?php else: ?>
                      N/A
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($row['class_name'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($row['subject_name'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($row['session']) ?></td>
                  <td><?= date('d-m-Y h:i A', strtotime($row['created_at'])) ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <span class="text-danger fw-bold">To Edit/Delete a Resource, Contact your Administrator</span>
        </div>
      <?php else: ?>
        <div class="alert alert-warning">No resources found for the selected filters.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php endif; ?>


<?php
include('assets/inc/footer.php');
?>
