<?php 
session_start();
include('assets/inc/header.php');

if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}


?>
<h3>Add Resources</h3>

<?php
include 'db_connect.php'; // db connection

// ================= FILTER HANDLING ===================
$class_id   = $_GET['class_id']   ?? '';
$subject_id = $_GET['subject_id'] ?? '';
$session    = $_GET['session']    ?? '';

// Build WHERE condition dynamically
$where = [];
if ($class_id != '')   $where[] = "r.class = '$class_id'";
if ($subject_id != '') $where[] = "r.subject = '$subject_id'";
if ($session != '')    $where[] = "r.session = '$session'";

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";


// ================= FETCH RESULTS FOR DISPLAY ===================
$sql = "SELECT r.student_id, s.name, c.class_name, sub.subject_name, r.term, r.session,
               r.first_ca, r.second_ca, r.exam, r.total
        FROM results r
        JOIN jss2_students_records s ON r.student_id = s.student_id
        JOIN classes c ON r.class = c.class_id
        JOIN jss2_subjects sub ON r.subject = sub.id
        $whereSQL
        ORDER BY c.class_id, sub.id, s.student_id";
$res = $conn->query($sql);

// Dropdown Data
$classes  = $conn->query("SELECT * FROM classes ORDER BY class_id");
$subjects = $conn->query("SELECT * FROM jss2_subjects ORDER BY id");
$sessions = $conn->query("SELECT DISTINCT session FROM results ORDER BY id DESC");
?>


<!-- ============PROCESS FORM DATA============ -->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form values safely
    $title       = $conn->real_escape_string($_POST['title'] ?? '');
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $type        = $conn->real_escape_string($_POST['resource_type'] ?? '');
    $url         = $conn->real_escape_string($_POST['resource_url'] ?? '');

    $filePath = null;
    $fileHash = null;

    // Handle file upload
    if ($type === 'file' && isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === 0) {
        $maxSize   = 100 * 1024 * 1024; // 100MB
        $fileSize  = $_FILES['resource_file']['size'];

        if ($fileSize > $maxSize) {
            echo "<div class='alert alert-danger'>Error: File size exceeds 100MB limit.</div>";
            return;
        }

        $uploadDir = "uploads/resources/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName   = time() . "_" . basename($_FILES['resource_file']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['resource_file']['tmp_name'], $targetPath)) {
            $filePath = $targetPath;
            $fileHash = md5_file($filePath);

            //Check for duplicate file by hash
            $check = $conn->prepare("SELECT id FROM resources WHERE file_hash = ? LIMIT 1");
            $check->bind_param("s", $fileHash);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                echo "<div class='alert alert-warning'>Duplicate file detected! This file is already uploaded.</div>";
                $check->close();
                unlink($filePath); // delete duplicate uploaded file
                return;
            }
            $check->close();
        } else {
            echo "<div class='alert alert-danger'>Error uploading file.</div>";
            return; 
        }
    }

    //Single insert for both file and non-file resources
    $stmt = $conn->prepare("INSERT INTO resources 
        (title, description, type, url, file_path, file_hash, class_id, subject_id, session, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->bind_param("sssssssss", 
        $title, 
        $description, 
        $type, 
        $url, 
        $filePath, 
        $fileHash,   // null for non-file types
        $class_id, 
        $subject_id, 
        $session
    );

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Resource added successfully! <a href='view_resources.php' class='alert-link' mt-4>View Resource Here</a></div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
?>

<!-- =============== FILTER FORM ================= -->
<form method="get" class="row mb-3" id="filterForm">
    <div class="col-md-3 mt-4">
        <label>Class</label>
        <select name="class_id" id="class_id" class="form-control" required>
            <option value="">--Select Class--</option>
            <?php while ($c = $classes->fetch_assoc()): ?>
                <option value="<?= $c['class_id'] ?>" <?= ($c['class_id'] == $class_id ? 'selected' : '') ?>>
                    <?= htmlspecialchars($c['class_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="col-md-3 mt-4">
        <label>Subject</label>
        <select name="subject_id" id="subject_name" class="form-control" required>
            <option value="">--Select Subject--</option>
            <?php while ($s = $subjects->fetch_assoc()): ?>
                <option value="<?= $s['id'] ?>" <?= ($s['id'] == $subject_id ? 'selected' : '') ?>>
                    <?= htmlspecialchars($s['subject_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="col-md-3 mt-4">
        <label>Session</label>
        <select name="session"  id="session"class="form-control" required>
            <option value="">--Select Session--</option>
            <?php while ($ss = $sessions->fetch_assoc()): ?>
                <option value="<?= $ss['session'] ?>" <?= ($ss['session'] == $session ? 'selected' : '') ?>>
                    <?= htmlspecialchars($ss['session']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="col-md-12 mt-3 mt-4">
        <button type="submit" class="btn btn-primary">Filter</button>
    </div>
</form>




<!-- =============== RESULTS TABLE ================= -->
<?php if ($class_id || $subject_id || $session): ?>

<div class="container-fluid mt-4 h-100">

  <!-- Add Resource Form -->
  <div class="card shadow-sm mt-4 mb-4">
    <div class="card-header bg-primary text-white">
      <strong>Add New Resource</strong>
    </div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data" id="uploadResource">
        <div class="mb-3">
          <label class="form-label">Title</label>
          <input type="text" class="form-control" name="title" placeholder="e.g. JSS2 - Basic Science Notes">
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea class="form-control" rows="2" name="description" placeholder="Short summary of the resource..."></textarea>
        </div>

        <!-- Type -->
        <div class="mb-3">
          <label class="form-label">Type</label>
          <select class="form-select" id="resourceType" name="resource_type" required>
            <option value="" selected disabled>-- Select Type --</option>
            <option value="file">PDF/Word/Excel/PowerPoint</option>
            <option value="video">Video</option>
            <option value="link">Link</option>
            <option value="other">Other</option>
          </select>
        </div>

        <!-- URL input (hidden by default) -->
        <div class="mb-3" id="urlGroup" style="display:none">
          <label class="form-label">Resource URL</label>
          <input type="url" class="form-control" id="resourceUrl" name="resource_url" placeholder="">
        </div>

        <!-- File input (hidden by default) -->
        <div class="mb-3" id="fileGroup" style="display:none">
          <label class="form-label">Attach Document</label>
          <input type="file" class="form-control" id="resourceFile" name="resource_file"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
        </div>

        <button type="button" class="btn btn-primary float-start mt-4" id="showConfirm">
          Add Resource
        </button>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>



<script>
document.addEventListener('DOMContentLoaded', function () {
  const typeSel   = document.getElementById('resourceType');
  const urlGroup  = document.getElementById('urlGroup');
  const fileGroup = document.getElementById('fileGroup');
  const urlInput  = document.getElementById('resourceUrl');
  const fileInput = document.getElementById('resourceFile');

  function toggleInputs() {
    const t = typeSel.value;

    // Hide both + clear + remove required
    urlGroup.style.display  = 'none';
    fileGroup.style.display = 'none';
    urlInput.required  = false;
    fileInput.required = false;
    // optional: clear values when switching type
    urlInput.value  = '';
    fileInput.value = '';
    urlInput.placeholder = '';

    if (t === 'file') {
      fileGroup.style.display = '';
      fileInput.required = true;
    } else if (t === 'video' || t === 'link' || t === 'other') {
      urlGroup.style.display = '';
      urlInput.required = true;
      urlInput.placeholder =
        t === 'video' ? 'Paste video link (e.g. https://www.example.com)'
      : t === 'link'  ? 'Paste website/resource link (e.g. https://www.example.com)'
                      : 'Paste resource URL or description link (e.g. https://www.example.com)';
    }
  }



  typeSel.addEventListener('change', toggleInputs);
  // keep both hidden on load; if a value is preselected, reflect it:
  if (typeSel.value) toggleInputs();


  document.getElementById("showConfirm").addEventListener("click", function () {
    let form = document.getElementById("uploadResource");

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    let className   = document.getElementById("class_id").selectedOptions[0]?.text || "";
    let subjectName = document.getElementById("subject_name").selectedOptions[0]?.text || "";
    let sessionName = document.getElementById("session").selectedOptions[0]?.text || "";

    Swal.fire({
        title: "Confirm Upload",
        html: `Are you sure you want to upload resource for:<br>
               <b>Class:</b> ${className}<br>
               <b>Subject:</b> ${subjectName}<br>
               <b>Session:</b> ${sessionName}<br>`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, Upload",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
});
</script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php include('assets/inc/footer.php'); ?>
