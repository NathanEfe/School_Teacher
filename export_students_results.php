<?php
require 'vendor/autoload.php';
include 'db_connect.php';
session_start();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


// Get filters
$class_id   = $_POST['class_id'];
$subject_id   = $_POST['subject_id'];
$session    = $_POST['session'];
$term       = $_POST['term'];
$staff_id = $_SESSION['staff_id'];

// Fetch subjects
$subjects = [];
$subRes = $conn->query("SELECT id, subject_name FROM jss2_subjects WHERE id = '$class_id'");
while ($row = $subRes->fetch_assoc()) {
    $subjects[$row['id']] = $row['subject_name'];
}


    // $classRes = $conn->query("SELECT class_name FROM classes WHERE class_id='" . $conn->real_escape_string($class_id) . "'");
    // $classRow = $classRes->fetch_assoc();
    // $className = $classRow['class_name'] ?? '';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

    // Insert logo
    $drawing = new Drawing();
    $drawing->setPath('assets/images/delsulogo.jpg');
    $drawing->setCoordinates('C2');
    $drawing->setHeight(80);
    $drawing->setWorksheet($sheet);

    // School Info Section
    $sheet->mergeCells('A7:H7');
    $sheet->setCellValue('A7', 'DELSU SECONDARY SCHOOL');
    $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(18);
    $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A8:H8');
    $sheet->setCellValue('A8', 'P.M.B 1, Abraka, Delta State, Nigeria');
    $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A9:H9');
    $sheet->setCellValue('A9', "Class: $class_id   Term: <?php ($term ?? 'All Terms') ?>  Session: $session");
    $sheet->getStyle('A9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A9')->getFont()->setBold(true);

    $sheet->mergeCells('A10:H10');
    $sheet->setCellValue('A10', 'Staff ID: ' . $staff_id);
    $sheet->getStyle('A10')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// ================= HEADER ====================
$headerRow1 = 13;
$headerRow2 = 14;



// Student ID & Name
$sheet->setCellValue("A$headerRow1", "Student ID");
$sheet->setCellValue("B$headerRow1", "Student Name");
$sheet->mergeCells("A$headerRow1:A$headerRow2");
$sheet->mergeCells("B$headerRow1:B$headerRow2");

$sheet->getColumnDimension("A")->setWidth(20);
$sheet->getColumnDimension("B")->setWidth(30);

// Subjects headers
$colIndex = 3;
foreach ($subjects as $subjectId => $subjectName) {
    $startCol = Coordinate::stringFromColumnIndex($colIndex);
    $endCol   = Coordinate::stringFromColumnIndex($colIndex + 4);

    // Merge subject header
    $sheet->mergeCells("$startCol$headerRow1:$endCol$headerRow1");
    $sheet->setCellValue("$startCol$headerRow1", $subjectName);

    // Sub-headers
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex)     . $headerRow2, '1st CA(20)');
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex + 1) . $headerRow2, '2nd CA(20)');
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex + 2) . $headerRow2, 'Exam(60)');
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex + 3) . $headerRow2, 'Total');
    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex + 4) . $headerRow2, 'Grade');

    $sheet->getStyle("$startCol$headerRow1:$endCol$headerRow2")->getFont()->setBold(true);

    // Column widths
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colIndex))->setWidth(12);
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colIndex + 1))->setWidth(12);
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colIndex + 2))->setWidth(12);
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colIndex + 3))->setWidth(14);
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colIndex + 4))->setWidth(18);

    $colIndex += 5;
}


                // Get subjects for table structure
                $subjectList = $conn->query("SELECT * FROM jss2_subjects ORDER BY id");
                $subjectsArr = [];
                while ($sub = $subjectList->fetch_assoc()) {
                    $subjectsArr[] = $sub['subject_name'];
                    echo "<th colspan='5' style='text-align:center'>" . htmlspecialchars($sub['subject_name']) . "</th>";
                }
                
// Extra columns at the end
$extraCols = ["Grand Total", "Average", "Position", "Remarks"];
foreach ($extraCols as $extra) {
    $colLetter = Coordinate::stringFromColumnIndex($colIndex);
    $sheet->mergeCells("$colLetter$headerRow1:$colLetter$headerRow2");
    $sheet->setCellValue("$colLetter$headerRow1", $extra);
    $sheet->getStyle("$colLetter$headerRow1")->getFont()->setBold(true);
    $sheet->getColumnDimension($colLetter)->setWidth(15);
    $colIndex++;
}

// ================= FETCH RESULTS ====================
$sql = "SELECT s.student_id, s.name, r.subject, r.first_ca, r.second_ca, r.exam, r.total, r.grade
        FROM results r 
        JOIN jss2_students_records s ON r.student_id = s.id 
        WHERE r.class = '$class_id' 
          AND r.session = '$session' 
          AND r.term = '$term'
        ORDER BY s.name";

$res = $conn->query($sql);

// Pivot results into [student][subject] = scores
$studentData = [];
while ($row = $res->fetch_assoc()) {
    $sid = $row['sid'];
    if (!isset($studentData[$sid])) {
        $studentData[$sid] = [
            'student_id' => $row['student_id'],
            'name'       => $row['name'],
            'subjects'   => []
        ];
    }
    if ($row['subject_id']) {
        $studentData[$sid]['subjects'][$row['subject_id']] = [
            'ca1'   => $row['ca1'],
            'ca2'   => $row['ca2'],
            'exam'  => $row['exam'],
            'total' => $row['total'],
            'grade' => $row['grade']
        ];
    }
}

// ================= WRITE RESULTS ====================
$rowIndex = 3;
foreach ($studentData as $student) {
    $dataRow = [$student['student_id'], $student['name']];
    $grandTotal = 0;
    $subjectCount = 0;

    foreach ($subjects as $subId => $subName) {
        if (isset($student['subjects'][$subId])) {
            $sub = $student['subjects'][$subId];
            $dataRow[] = $sub['ca1'];
            $dataRow[] = $sub['ca2'];
            $dataRow[] = $sub['exam'];
            $dataRow[] = $sub['total'];
            $dataRow[] = $sub['grade'];

            $grandTotal += (int)$sub['total'];
            $subjectCount++;
        } else {
            // empty subject
            $dataRow[] = '';
            $dataRow[] = '';
            $dataRow[] = '';
            $dataRow[] = '';
            $dataRow[] = '';
        }
    }
}

// $filename = "Exported_Results.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=Result_Session.xlsx");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
