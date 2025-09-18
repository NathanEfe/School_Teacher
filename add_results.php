<?php 
session_start();
include('assets/inc/header.php'); 
include('db_connect.php'); 
if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}



// Fetch classes
$classes = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC");

//Fetch Subjects
$subjects = $conn->query("SELECT id, subject_name FROM jss2_subjects ORDER BY subject_name ASC");

// Fetch terms & sessions
$terms_sessions = $conn->query("SELECT DISTINCT session, term FROM school ORDER BY session DESC");

// Get logged-in teacher ID
$staff_id = $_SESSION['staff_id'];

// ================== PAGE LOAD (Filter button) ==================
$students = [];
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['class_id'], $_GET['session'], $_GET['term'] , $_GET['subject_name'])) {
    $class = $_GET['class_id'];
    $session_year = $_GET['session'];
    $term = $_GET['term'];
    $subject = $_GET['subject_name'];

    // Fetch students for this class
    $query = $conn->prepare("SELECT student_id, name FROM jss2_students_records WHERE class_id = ?");
    $query->bind_param("s", $class);
    $query->execute();
    $students = $query->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<h3 class="mb-4">Download Results Template</h3>
<!-- Filter Form -->
 <div class="card">
  <div class="card-header bg-info text-white">Download Result Template</div>
  <div class="card-body">
    <div class="alert alert-warning mb-4">
  Please Select the <span class="alert-link"> Correct Class, Session, and Term </span> you would like to download the result template for (all subjects will be included).
</div>
      <form method="GET" class="row mb-4">
        <div class="col-md-4 mt-4">
          <label class="form-label">Class</label>
          <select class="form-select" name="class_id" required>
            <option value="">-- Select Class --</option>
            <?php while ($row = $classes->fetch_assoc()) { ?>
              <option value="<?= $row['class_id']; ?>" <?= (isset($_GET['class_id']) && $_GET['class_id'] == $row['class_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($row['class_name']); ?>
              </option>
            <?php } ?>
          </select>
        </div>

        <div class="col-md-4 mt-4">
          <label class="form-label">Subject</label>
          <select class="form-select" name="subject_name" required>
            <option value="">-- Select Subject --</option>
            <?php while ($row = $subjects->fetch_assoc()) { ?>
              <option value="<?= $row['id']; ?>" <?= (isset($_GET['subject_name']) && $_GET['subject_name'] == $row['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($row['subject_name']); ?>
              </option>
            <?php } ?>
          </select>
        </div>

        <div class="col-md-4 mt-4">
          <label class="form-label">Session</label>
          <select class="form-select" name="session" required>
            <option value="">-- Select Session --</option>
            <?php 
            $session_list = [];
            $terms_sessions->data_seek(0);
            while ($row = $terms_sessions->fetch_assoc()) {
                if (!in_array($row['session'], $session_list)) {
                    $session_list[] = $row['session'];
                    $selected = (isset($_GET['session']) && $_GET['session'] == $row['session']) ? 'selected' : '';
                    echo "<option value='".htmlspecialchars($row['session'])."' $selected>".$row['session']."</option>";
                }
            } ?>
          </select>
        </div>

        <div class="col-md-4 mt-4">
          <label class="form-label">Term</label>
          <select class="form-select" name="term" required>
            <option value="">-- Select Term --</option>
            <?php 
            $term_list = [];
            $terms_sessions->data_seek(0);
            while ($row = $terms_sessions->fetch_assoc()) {
                if (!in_array($row['term'], $term_list)) {
                    $term_list[] = $row['term'];
                    $selected = (isset($_GET['term']) && $_GET['term'] == $row['term']) ? 'selected' : '';
                    echo "<option value='".htmlspecialchars($row['term'])."' $selected>".$row['term']."</option>";
                }
            } ?>
          </select>
        </div>

        <!-- Filter Button -->
        <div class="col-md-12 text-start mt-3">
          <button type="submit" class="btn btn-info">Filter</button>
        </div>
      </form>
    </div>
  </div>


<?php if (!empty($students)) { ?>
  <!-- Download Template Button -->
  <?php
    // Fetch class name
    $classRes = $conn->query("SELECT class_name FROM classes WHERE class_id = '$class'");
    $classRow = $classRes->fetch_assoc();
    $className = $classRow['class_name'];

// Fetch subject name (keep ID separate)
$subjectId = $subject; 
$subjectRes = $conn->query("SELECT subject_name FROM jss2_subjects WHERE id = '$subjectId'");
$subjectRow = $subjectRes->fetch_assoc();
$subjectName = $subjectRow['subject_name'];

  ?>
  <div class="card">
    <div class="card-header bg-success text-white">Download Template</div>
      <div class="card-body">
        <p class="text-muted text-danger mb-4">
            Please download the result template for 
            <strong><?php echo $className . ' (' . $term . ' Term, ' . $session_year . ', ' . $subjectName . ')'; ?></strong>.          </p>
        <form action="download_template.php" method="post" class="mb-3">
          <input type="hidden" name="class_id" value="<?= htmlspecialchars($class) ?>">
          <input type="hidden" name="session" value="<?= htmlspecialchars($session_year) ?>">
          <input type="hidden" name="term" value="<?= htmlspecialchars($term) ?>">
          <input type="hidden" name="subject_id" value="<?= htmlspecialchars($subjectId) ?>">
            <button type="submit" class="btn btn-success">
                Download Template
            </button>
        </form>
      </div>
  </div>
<?php } ?>

<?php include('assets/inc/footer.php'); ?>
