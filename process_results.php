<?php
include('db_connect.php'); 

// Fetch classes
$classes = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC");

// Fetch subjects
$subjects = $conn->query("SELECT id, subject_name FROM jss2_subjects ORDER BY id ASC");

// Fetch terms & sessions
$terms_sessions = $conn->query("SELECT DISTINCT session, term FROM school ORDER BY session DESC");

// Get logged-in teacher ID
$staff_id = $_SESSION['staff_id'] ?? null;

// Handle result submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_results'])) {
    $class = $_POST['class_id'];
    $subject = $_POST['subject_id'];
    $term = $_POST['term'];
    $session_year = $_POST['session'];

    $alreadyExists = false;

    foreach ($_POST['first_ca'] as $student_id => $first_ca) {
        $second_ca = $_POST['second_ca'][$student_id] ?? 0;
        $exam = $_POST['exam'][$student_id] ?? 0;
        $total = $_POST['total'][$student_id] ?? ($first_ca + $second_ca + $exam);

        // 🔹 Check if result already exists
        $check = $conn->prepare("SELECT id FROM results 
            WHERE student_id=? AND class=? AND subject=? AND term=? AND session=?");
        $check->bind_param("sssss", $student_id, $class, $subject, $term, $session_year);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $alreadyExists = true;
            break; // stop checking further, no need to insert
        }

        // 🔹 Insert only if not found
        $stmt = $conn->prepare("INSERT INTO results 
            (student_id, class, subject, term, session, first_ca, second_ca, exam, total, staff_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssiiii", 
            $student_id, $class, $subject, $term, $session_year, 
            $first_ca, $second_ca, $exam, $total, $staff_id
        );
        $stmt->execute();
    }

    if ($alreadyExists) {
        header("Location: add_results.php?msg=already_taken");
        exit();
    } else {
        echo "<div class='alert alert-success'>Results added successfully!</div>";
    }
}



// Fetch students dynamically (if class selected)
$students = [];
if (!empty($_GET['class_id'])) {
    $class = $_GET['class_id'];
    $query = $conn->prepare("SELECT student_id, name FROM jss2_students_records WHERE class_id = ?");
    $query->bind_param("s", $class);
    $query->execute();
    $students = $query->get_result()->fetch_all(MYSQLI_ASSOC);
}


?>