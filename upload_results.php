<?php include('assets/inc/header.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
require 'db_connect.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['result_file'])) {
    $uploadedFile = $_FILES['result_file']['tmp_name'];
    $class   = $conn->real_escape_string($_POST['class_id']);
    $session = $conn->real_escape_string($_POST['session']);
    $term    = $conn->real_escape_string($_POST['term']);
    $subject = $conn->real_escape_string($_POST['subject_id']); // ✅ selected subject

    try {
        $spreadsheet = IOFactory::load($uploadedFile);
        $sheet = $spreadsheet->getActiveSheet();

        // ✅ Extract Staff ID (row 10, cell A10)
        $staffCell = $sheet->getCell('A10')->getValue();
        $staff_id  = trim(str_replace('Staff ID:', '', $staffCell));

        $inserted = 0;

        // ✅ Loop through students (starting row 15)
        foreach ($sheet->getRowIterator(15) as $row) {
            $rowIndex  = $row->getRowIndex();
            $studentId = trim($sheet->getCell("A{$rowIndex}")->getValue());
            $studentName = trim($sheet->getCell("B{$rowIndex}")->getValue());

            if (!$studentId || !$studentName) continue;

            // ✅ Only one subject upload, fixed columns (C to G)
            $firstCA  = (int) $sheet->getCell("C{$rowIndex}")->getCalculatedValue();
            $secondCA = (int) $sheet->getCell("D{$rowIndex}")->getCalculatedValue();
            $exam     = (int) $sheet->getCell("E{$rowIndex}")->getCalculatedValue();
            $total    = (int) $sheet->getCell("F{$rowIndex}")->getCalculatedValue();
            $grade    = trim((string) $sheet->getCell("G{$rowIndex}")->getCalculatedValue());

            if ($firstCA !== 0 || $secondCA !== 0 || $exam !== 0) {
                $stmt = $conn->prepare("
                    INSERT INTO results 
                    (student_id, class, subject, session, term, first_ca, second_ca, exam, total, grade, staff_id) 
                    VALUES (?,?,?,?,?,?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE 
                      first_ca=VALUES(first_ca), 
                      second_ca=VALUES(second_ca), 
                      exam=VALUES(exam), 
                      total=VALUES(total), 
                      grade=VALUES(grade),
                      staff_id=VALUES(staff_id)
                ");

                if (!$stmt) {
                    throw new Exception("MySQL Prepare failed: " . $conn->error);
                }

                $stmt->bind_param(
                    "sssssiiiiss",
                    $studentId,
                    $class,
                    $subject,
                    $session,
                    $term,
                    $firstCA,
                    $secondCA,
                    $exam,
                    $total,
                    $grade,
                    $staff_id
                );

                if ($stmt->execute()) {
                    $inserted++;
                } else {
                    throw new Exception("MySQL Execute failed: " . $stmt->error);
                }
            }
        }

        echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Upload Complete',
            html: 'Successfully uploaded <b>{$inserted}</b> results.<br><a href=\"view_results.php\" class=\"btn btn-sm btn-success mt-2\">View Results</a>'
        });
        </script>";

    } catch (Exception $e) {
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Upload Failed',
            text: '" . addslashes($e->getMessage()) . "'
        });
        </script>";
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Upload Results</title>
</head>
<body>
    <h3>Upload Results</h3>

    <div class="card mt-4">
        <div class="card-header bg-info text-white">Upload Results</div>
        <div class="card-body">
            <form id="uploadForm" action="upload_results.php" method="POST" enctype="multipart/form-data">
                <div class="alert alert-warning">
                    Please Select the <span class="alert-link">Correct Class, Session, Term, and Subject</span> you would like to upload the result template for.
                </div>

                <!-- Class Filter -->
                <div class="col-md-3 mt-4">
                    <label class="text-secondary">Class:</label>
                    <select name="class_id" id="class_id" class="form-control" required>
                        <option value="">--Select Class--</option>
                        <?php
                        $classRes = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name");
                        while ($row = $classRes->fetch_assoc()) {
                            echo "<option value='{$row['class_id']}'>{$row['class_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Session Filter -->
                <div class="col-md-3 mt-4">
                    <label class="text-secondary">Session:</label>
                    <select name="session" id="session" class="form-control" required>
                        <option value="">--Select Session--</option>
                        <?php
                        $sessRes = $conn->query("SELECT DISTINCT session FROM school ORDER BY session DESC");
                        while ($row = $sessRes->fetch_assoc()) {
                            echo "<option value='{$row['session']}'>{$row['session']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Term Filter -->
                <div class="col-md-3 mt-4">
                    <label class="text-secondary">Term:</label>
                    <select name="term" id="term" class="form-control" required>
                        <option value="">--Select Term--</option>
                        <?php
                        $termRes = $conn->query("SELECT DISTINCT term FROM school ORDER BY id DESC");
                        while ($row = $termRes->fetch_assoc()) {
                            echo "<option value='{$row['term']}'>{$row['term']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- ✅ Subject Filter -->
                <div class="col-md-3 mt-4">
                    <label class="text-secondary">Subject:</label>
                    <select name="subject_id" id="subject_id" class="form-control" required>
                        <option value="">--Select Subject--</option>
                        <?php
                        $subRes = $conn->query("SELECT id, subject_name FROM jss2_subjects ORDER BY subject_name ASC");
                        while ($row = $subRes->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['subject_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="container-fluid mt-4">
                    <!-- Upload File -->
                    <label>Upload Excel File:</label>
                    <input type="file" name="result_file" accept=".xlsx" class="form-control" required>

                    <!-- Trigger SweetAlert -->
                    <button type="button" class="btn btn-primary mt-4" id="showConfirm">Upload Results</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        document.getElementById("showConfirm").addEventListener("click", function() {
            let form = document.getElementById("uploadForm");

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            let className = document.getElementById("class_id").selectedOptions[0]?.text || "";
            let sessionName = document.getElementById("session").selectedOptions[0]?.text || "";
            let termName = document.getElementById("term").selectedOptions[0]?.text || "";
            let subjectName = document.getElementById("subject_id").selectedOptions[0]?.text || "";

            Swal.fire({
                title: "Confirm Upload",
                html: `Are you sure you want to upload results for:<br>
               <b>Class:</b> ${className}<br>
               <b>Session:</b> ${sessionName}<br>
               <b>Term:</b> ${termName}<br>
               <b>Subject:</b> ${subjectName}`,
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
    </script>
</body>
</html>

<?php include('assets/inc/footer.php'); ?>
