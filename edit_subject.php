<?php
session_start();
include('assets/inc/header.php');

if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}
include 'db_connect.php';

$classes = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC");


// ================== VALIDATE INPUT ==================
$subject_id = $_GET['id'] ?? '';
if (empty($subject_id)) {
    echo "<div class='alert alert-danger'>No subject selected for editing.</div>";
    include('assets/inc/footer.php');
    exit;
}

// ================== FETCH SUBJECT INFO ==================
$stmt = $conn->prepare("
    SELECT * FROM jss2_subjects s
    LEFT JOIN classes c ON s.id = c.class_id
    WHERE id = ?
");
$stmt->bind_param("s", $subject_id);
$stmt->execute();
$subject = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$subject) {
    echo "<div class='alert alert-danger'>Subject not found.</div>";
    include('assets/inc/footer.php');
    exit;
}

// ================== UPDATE HANDLER ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name']);
    $code         = trim($_POST['code']);

    // Get selected classes (array from checkboxes)
    $selected_classes = $_POST['classes'] ?? [];

    // Save as comma-separated string (not ideal, but works if your DB column is varchar)
    $class = implode(',', $selected_classes);

    $sql = "UPDATE jss2_subjects
            SET subject_name = ?, code = ?, class = ?, updated_on = NOW()
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $subject_name, $code, $class, $subject_id);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Subject details updated successfully. 
                <a href="subjects_overview.php?id=' . urlencode($subject['id']) . '" class="alert-link">Go Back</a>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';

        $subject['subject_name'] = $subject_name;
        $subject['code']         = $code;
        $subject['class']        = $class;
    } else {
        echo "<div class='alert alert-danger'>Error updating record: " . $stmt->error . "</div>";
    }

    $stmt->close();
}
$assigned_classes = explode(',', $subject['class'] ?? '');

?>

<h3 class="h3 mb-4">Edit Subject</h3>

<div class="card mb-4 shadow-sm">
  <div class="card-body">
    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Subject Name</label>
        <input type="text" name="subject_name" class="form-control" value="<?= htmlspecialchars($subject['subject_name']) ?>" required>
      </div>

      <div class="mb-3">
          <label class="form-label">Code</label>
          <input type="text" class="form-control" name="code" value="<?= htmlspecialchars($subject['code']) ?>">
      </div>

<div class="col-md-12 mt-4">
    <label for="">Assign Class:</label>
    <div class="row">
        <?php
        $classes = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_id ASC");
        while ($row = $classes->fetch_assoc()) { ?>
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" 
                    name="classes[]" 
                    value="<?= $row['class_name']; ?>" 
                    id="subject_<?= $row['class_id']; ?>"
                    <?= in_array($row['class_name'], $assigned_classes) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="subject_<?= $row['class_id']; ?>">
                        <?= htmlspecialchars($row['class_name']); ?>
                    </label>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

     

      <button type="submit" class=" mt-4 btn btn-success">Update Subject</button>
      <a href="subjects_overview.php" class="btn btn-secondary">Go Back</a>
    </form>
  </div>
</div>

<?php include('assets/inc/footer.php'); ?>
