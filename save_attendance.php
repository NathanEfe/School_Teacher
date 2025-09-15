<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = intval($_POST['class_id']);
    $date     = $_POST['date'];
    $session = $_POST['session'];

    if (!empty($_POST['attendance']) && !empty($_POST['student_id'])) {
            try {
            foreach ($_POST['student_id'] as $index => $stud_id) {
            $student_id   = $conn->real_escape_string($stud_id);
            $student_name = $conn->real_escape_string($_POST['student_name'][$stud_id]);

            // Get selected status or default to Absent
            if (isset($_POST['attendance'][$stud_id])) {
                $status = $conn->real_escape_string($_POST['attendance'][$stud_id]);
            } else {
                $status = 'Absent';
            }

            $sql = "INSERT INTO attendance (student_id, class_id, name, date, status, session)
                    VALUES ('$student_id', '$class_id', '$student_name', '$date', '$status', '$session')";
            $conn->query($sql);
        }


            // If all inserts succeed
            header("Location: take_attendance.php?msg=success");
            exit;

        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $session = $conn->real_escape_string($_POST['session']);
                $date    = $conn->real_escape_string($_POST['date']);
                header("Location: take_attendance.php?msg=already_taken&session=$session&date=$date");
                exit;
            } else {
                header("Location: take_attendance.php?msg=error");
                exit;
            }
        }
    }

} else {
    echo "Invalid request.";
}
?>


If I insert into morning session and try to insert into evening session Its giving msg = already taken instead of inserting into evening session. 