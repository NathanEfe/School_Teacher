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
    $name          = trim($_POST['name']);
    $dob           = trim($_POST['date_of_birth']);
    $parent_name   = trim($_POST['parent_name']);
    $mobile_number = trim($_POST['mobile_number']);
    $address       = trim($_POST['address']);
    $class_id      = trim($_POST['class_id']);

    // Handle profile picture
    $profile_pic_path = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/students/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $new_name = "profile_" . uniqid() . "." . $ext;
        $target_file = $upload_dir . $new_name;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_pic_path = $target_file;
        } else {
            echo "<div class='alert alert-danger'>Failed to upload profile picture.</div>";
        }
    }

    // 🔹 Check for duplicate student (same name + dob)
    $dup_stmt = $conn->prepare("SELECT id FROM jss2_students_records WHERE name = ? AND date_of_birth = ?");
    $dup_stmt->bind_param("ss", $name, $dob);
    $dup_stmt->execute();
    $dup_stmt->store_result();

    if ($dup_stmt->num_rows > 0) {
        echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                Student <strong>$name</strong> with Date of Birth <strong>$dob</strong> already exists!
                <a href='students_overview.php' class='alert-link'>View Students</a>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
    } else {
        // 🔹 Find the latest student_id in the database
        $last_id_res = $conn->query("SELECT student_id FROM jss2_students_records 
                                    ORDER BY id DESC LIMIT 1");
        $last_id_row = $last_id_res->fetch_assoc();

        if ($last_id_row && preg_match('/^DSS(\d+)$/', $last_id_row['student_id'], $matches)) {
            $next_number = (int)$matches[1] + 1; // increment last number
        } else {
            $next_number = 3; // first record will be DSS003
        }

        // 🔹 Format new ID (6 digits padding)
        $generated_id = "DSS" . str_pad($next_number, 6, "0", STR_PAD_LEFT);

        // 🔹 Insert new student WITH generated_id
        $stmt = $conn->prepare("INSERT INTO jss2_students_records 
            (student_id, name, class_id, date_of_birth, parent_name, mobile_number, address, profile_picture, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param("ssisssss", 
            $generated_id, $name, $class_id, $dob, $parent_name, $mobile_number, $address, $profile_pic_path
        );

        if ($stmt->execute()) {
            echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    Student Added Successfully. Assigned ID: <strong>{$generated_id}</strong>
                    <a href='students_overview.php' class='alert-link'>View Students</a>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
    }
}
?>
<h3 class="mb-4">Add Student</h3>
<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        Add Student
    </div>
    <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="col-md-3">
                <label for="">Attach Picture:</label>
                <img src="./assets/images/user/avatar-2.png" alt="" class="img-responsive mt-4">
                <input type="file" name="profile_picture" class="form-control mt-4" accept="image/png, image/jpg, image/jpeg, image/gif">
            </div>
            <div class="col-md-3 mt-4">
                <label for="">Full Name:</label>
                <input type="text" name="name" class="form-control" placeholder="John Okafor" required>
            </div>
            <div class="col-md-3 mt-4">
                <label for="">Date of Birth:</label>
                <input type="date" name="date_of_birth" class="form-control" max="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-3 mt-4">
                <label for="">Parent/Guardian Name:</label>
                <input type="text" name="parent_name" class="form-control" placeholder="Moses Okafor" required>
            </div>
            <div class="col-md-3 mt-4">
                <label for="">Parent/Guardian Phone Number:</label>
                <input type="number" name="mobile_number" class="form-control" placeholder="09087654310" required>
            </div>
            <div class="col-md-3 mt-4">
                <label for="">House Address:</label>
                <textarea name="address" class="form-control" placeholder="Enter Address Here" required></textarea>
            </div>
            <div class="col-md-3 mt-4">
                <label for="">Assigned Class:</label>
                <select class="form-select" name="class_id" required>
                    <option value="">-- Select Class --</option>
                    <?php while ($row = $classes->fetch_assoc()) { ?>
                        <option value="<?= $row['class_id']; ?>">
                            <?= htmlspecialchars($row['class_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Add Student</button>
        </form>
    </div>
</div>

<?php include('assets/inc/footer.php'); ?>
