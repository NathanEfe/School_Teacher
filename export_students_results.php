<?php
require 'vendor/autoload.php';
include 'db_connect.php';
session_start();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;


// ================= FILTERS =================
$class_id   = $_POST['class_id']   ?? '';
$subject_id = $_POST['subject_id'] ?? '';
$session    = $_POST['session']    ?? '';
$term       = $_POST['term']       ?? '';
$staff_id   = $_SESSION['staff_id'] ?? 'Unknown';

$where = [];
if ($class_id != '')   $where[] = "r.class = '$class_id'";
if ($subject_id != '') $where[] = "r.subject = '$subject_id'";
if ($session != '')    $where[] = "r.session = '$session'";
if ($term != '')       $where[] = "r.term = '$term'";

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";
$allTermsSelected = empty($term);

// ================= FETCH SUBJECTS =================
$subjectsArr = [];
$subjectList = $conn->query("SELECT * FROM jss2_subjects ORDER BY id");
while ($sub = $subjectList->fetch_assoc()) {
    $subjectsArr[] = $sub['subject_name'];
}

// ================= FETCH RESULTS =================
$sql = "SELECT r.student_id, s.name, c.class_name, sub.subject_name, r.term, r.session,
               r.first_ca, r.second_ca, r.exam, r.total, r.grade
        FROM results r
        JOIN jss2_students_records s ON r.student_id = s.student_id
        JOIN classes c ON r.class = c.class_id
        JOIN jss2_subjects sub ON r.subject = sub.id
        $whereSQL
        ORDER BY c.class_id, sub.id, s.student_id";
$res = $conn->query($sql);

//=============== FETCH CLASS ===============
    $classRes = $conn->query("SELECT class_name FROM classes WHERE class_id='" . $conn->real_escape_string($class_id) . "'");
    $classRow = $classRes->fetch_assoc();
    $className = $classRow['class_name'] ?? '';
    
// ================= GROUP RESULTS =================
$resultsByStudent = [];
while ($row = $res->fetch_assoc()) {
    $sid = $row['student_id'];
    $resultsByStudent[$sid]['info'] = [
        'student_id' => $row['student_id'],
        'name'       => $row['name'],
        'class_name' => $row['class_name'],
        'session'    => $row['session'],
    ];
    if ($allTermsSelected) {
        $termMap = [
            'First'  => '1st Term',
            'Second' => '2nd Term',
            'Third'  => '3rd Term',
        ];
        $termKey = $termMap[$row['term']] ?? $row['term'];
        $resultsByStudent[$sid]['subjects'][$row['subject_name']][$termKey] = $row['total'];
    } else {
        $resultsByStudent[$sid]['subjects'][$row['subject_name']] = [
            'first_ca'  => $row['first_ca'],
            'second_ca' => $row['second_ca'],
            'exam'      => $row['exam'],
            'total'     => $row['total'],
            'grade'     => $row['grade'],
        ];
    }
}

// ================= CALCULATE RANKING =================
$studentTotals = [];
foreach ($resultsByStudent as $sid => $student) {
    $overallTotal = 0;
    $subjectCount = 0;
    foreach ($student['subjects'] as $subj => $scores) {
        if ($allTermsSelected) {
            $grandTotal = ($scores['1st Term'] ?? 0) + ($scores['2nd Term'] ?? 0) + ($scores['3rd Term'] ?? 0);
            $overallTotal += $grandTotal;
            $subjectCount++;
        } else {
            $overallTotal += (int)($scores['total'] ?? 0);
            $subjectCount++;
        }
    }
    $studentTotals[$sid] = [
        'total' => $overallTotal,
        'avg'   => $subjectCount > 0 ? round($overallTotal / $subjectCount, 2) : 0
    ];
}
uasort($studentTotals, fn($a, $b) => $b['total'] <=> $a['total']);
$rankings = [];
$position = 0;
$prevTotal = null;
foreach ($studentTotals as $sid => $data) {
    if ($prevTotal !== null && $data['total'] == $prevTotal) {
        $rankings[$sid] = $position;
    } else {
        $position++;
        $rankings[$sid] = $position;
    }
    $prevTotal = $data['total'];
}

// ================= BUILD SPREADSHEET =================
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
    $sheet->setCellValue('A9', "Class: $className   Term: $term   Session: $session");
    $sheet->getStyle('A9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A9')->getFont()->setBold(true);

    $sheet->mergeCells('A10:H10');
    $sheet->setCellValue('A10', 'Staff ID: ' . $staff_id);
    $sheet->getStyle('A10')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


$row1 = 13;
$row2 = 14;
$col = 1;

// Helper to set cell by numeric index
function setCell($sheet, $col, $row, $value) {
    $cell = Coordinate::stringFromColumnIndex($col) . $row;
    $sheet->setCellValue($cell, $value);
}

// Basic student info
setCell($sheet, $col, $row1, "Student ID");
$sheet->mergeCells(Coordinate::stringFromColumnIndex($col).$row1.':'.Coordinate::stringFromColumnIndex($col).$row2);
$col++;

setCell($sheet, $col, $row1, "Name");
$sheet->mergeCells(Coordinate::stringFromColumnIndex($col).$row1.':'.Coordinate::stringFromColumnIndex($col).$row2);
$col++;

setCell($sheet, $col, $row1, "Class");
$sheet->mergeCells(Coordinate::stringFromColumnIndex($col).$row1.':'.Coordinate::stringFromColumnIndex($col).$row2);
$col++;

setCell($sheet, $col, $row1, "Session");
$sheet->mergeCells(Coordinate::stringFromColumnIndex($col).$row1.':'.Coordinate::stringFromColumnIndex($col).$row2);
$col++;

// Subjects
foreach ($subjectsArr as $subj) {
    $startCol = $col;
    setCell($sheet, $col, $row1, $subj);
    $endCol = $allTermsSelected ? $col + 4 : $col + 4;
    $sheet->mergeCells(Coordinate::stringFromColumnIndex($startCol).$row1.':'.Coordinate::stringFromColumnIndex($endCol).$row1);

    if ($allTermsSelected) {
        setCell($sheet, $col++, $row2, "1st Term");
        setCell($sheet, $col++, $row2, "2nd Term");
        setCell($sheet, $col++, $row2, "3rd Term");
        setCell($sheet, $col++, $row2, "Grand Total");
        setCell($sheet, $col++, $row2, "Average");
    } else {
        setCell($sheet, $col++, $row2, "1st CA");
        setCell($sheet, $col++, $row2, "2nd CA");
        setCell($sheet, $col++, $row2, "Exam");
        setCell($sheet, $col++, $row2, "Total");
        setCell($sheet, $col++, $row2, "Grade");
    }
}

// Extra columns
$extraCols = ["Grand Total", "Average", "Position", "Remarks"];
foreach ($extraCols as $extra) {
    setCell($sheet, $col, $row1, $extra);
    $sheet->mergeCells(Coordinate::stringFromColumnIndex($col).$row1.':'.Coordinate::stringFromColumnIndex($col).$row2);
    $col++;
}

// ================= DATA ROWS =================
$rowIndex = 15;
foreach ($resultsByStudent as $sid => $student) {
    $col = 1;
    setCell($sheet, $col++, $rowIndex, $student['info']['student_id']);
    setCell($sheet, $col++, $rowIndex, $student['info']['name']);
    setCell($sheet, $col++, $rowIndex, $student['info']['class_name']);
    setCell($sheet, $col++, $rowIndex, $student['info']['session']);

    $grandTotal = 0;
    $subjectCount = 0;

    foreach ($subjectsArr as $subj) {
        if ($allTermsSelected) {
            $t1 = $student['subjects'][$subj]['1st Term'] ?? 0;
            $t2 = $student['subjects'][$subj]['2nd Term'] ?? 0;
            $t3 = $student['subjects'][$subj]['3rd Term'] ?? 0;
            $gT = ($t1 ?: 0) + ($t2 ?: 0) + ($t3 ?: 0);
            $avg = ($t1 || $t2 || $t3) ? round($gT / (($t1?1:0)+($t2?1:0)+($t3?1:0)), 2) : 0;
            setCell($sheet, $col++, $rowIndex, $t1 ?: '-');
            setCell($sheet, $col++, $rowIndex, $t2 ?: '-');
            setCell($sheet, $col++, $rowIndex, $t3 ?: '-');
            setCell($sheet, $col++, $rowIndex, $gT);
            setCell($sheet, $col++, $rowIndex, $avg);
            $grandTotal += $gT; $subjectCount++;
        } else {
            $data = $student['subjects'][$subj] ?? [];
            setCell($sheet, $col++, $rowIndex, $data['first_ca'] ?? '-');
            setCell($sheet, $col++, $rowIndex, $data['second_ca'] ?? '-');
            setCell($sheet, $col++, $rowIndex, $data['exam'] ?? '-');
            setCell($sheet, $col++, $rowIndex, $data['total'] ?? '-');
            setCell($sheet, $col++, $rowIndex, $data['grade'] ?? '-');
            if (!empty($data['total'])) {
                $grandTotal += (int)$data['total']; $subjectCount++;
            }
        }
    }

    $avg = $subjectCount > 0 ? round($grandTotal / $subjectCount, 2) : 0;
    setCell($sheet, $col++, $rowIndex, $grandTotal);
    setCell($sheet, $col++, $rowIndex, $avg);
    setCell($sheet, $col++, $rowIndex, $rankings[$sid] ?? '-');
    setCell($sheet, $col++, $rowIndex, ""); // remarks placeholder

    $rowIndex++;
}

// ================= APPLY FORMATTING =================

// Auto-size all columns
foreach (range(1, $col) as $c) {
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
}

// Bold + centered headers
$headerRange = "A13:" . Coordinate::stringFromColumnIndex($col-1) . "13";
$sheet->getStyle($headerRange)->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
        'wrapText'   => true
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['argb' => '000000'],
        ]
    ],
    'fill' => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFDCE6F1'], // light blue background
    ],
]);

// Borders for data rows
$dataRange = "A14:" . Coordinate::stringFromColumnIndex($col-1) . ($rowIndex-1);
$sheet->getStyle($dataRange)->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['argb' => '000000'],
        ]
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
    ],
]);

// Freeze header row
$sheet->freezePane("A15");


    // Protect the entire sheet
    $sheet->getProtection()->setSheet(true);
    $sheet->getProtection()->setPassword('Password');

// ================= OUTPUT =================
$filename = "Students_Results.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
