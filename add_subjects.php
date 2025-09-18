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

// =========== HANDLE FORM SUBMISSION ===========
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_name  = trim($_POST['subject_name']);
    $code           = trim($_POST['code']);


    // 🔹 Check for duplicate subject (name)
    $dup_stmt = $conn->prepare("SELECT id FROM jss2_subjects WHERE subject_name = ?");
    $dup_stmt->bind_param("s", $subject_name);
    $dup_stmt->execute();
    $dup_stmt->store_result();

    if ($dup_stmt->num_rows > 0) {
        echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                Subject: <strong>$subject_name</strong> already exists!
                <a href='subjects_overview.php' class='alert-link'>View Subjects</a>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
    } else 
    
    {        // 🔹 Insert new subject 
        $stmt = $conn->prepare("INSERT INTO jss2_subjects 
            (subject_name, code, created_at) 
            VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", 
            $subject_name, $code);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    Subject Added Successfully.
                    <a href='subjects_overview.php' class='alert-link'>View Subjects</a>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
    }
}
?>
<h3 class="mb-4">Add Subject</h3>
<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        Add Subject
    </div>
    <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="col-md-3 mt-4">
                <label for="">Subject Name:</label>
                <input type="text" name="subject_name" class="form-control" placeholder="Agriculture" required>
            </div>
            <div class="col-md-3 mt-4">
                <label for="">Code:</label>
                <input type="text" name="code" class="form-control" placeholder="AGR101" required>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Add Subject</button>
        </form>
    </div>
</div>

<?php include('assets/inc/footer.php'); ?>
