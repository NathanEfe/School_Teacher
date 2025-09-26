<?php
require 'vendor/autoload.php'; // PhpSpreadsheet
include 'db_connect.php';

session_start();
if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;



// ================= FILTERS =================
$class_id   = $_POST['class_id']   ?? '';
$session    = $_POST['session']    ?? '';
$term       = $_POST['term']       ?? '';
$staff_id   = $_SESSION['staff_id'] ?? 'Unknown';

$where = [];
if ($class_id != '')   $where[] = "r.class = '$class_id'";
if ($session != '')    $where[] = "r.session = '$session'";
if ($term != '')       $where[] = "r.term = '$term'";

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

//=============== FETCH CLASS ===============
$classRes = $conn->query("SELECT class_name FROM classes WHERE class_id='" . $conn->real_escape_string($class_id) . "'");
$classRow = $classRes->fetch_assoc();
$className = $classRow['class_name'] ?? '';

// ================= FETCH SUBJECTS ===================
$subjectsRes = $conn->query("SELECT id, subject_name FROM jss2_subjects ORDER BY id");
$subjects = [];
while ($sub = $subjectsRes->fetch_assoc()) {
    $subjects[$sub['id']] = $sub['subject_name'];
}

// ================= FETCH STUDENT RESULTS ===================
$sql = "SELECT r.student_id, s.name, c.class_name, r.subject, r.total
        FROM results r
        JOIN jss2_students_records s ON r.student_id = s.student_id
        JOIN classes c ON r.class = c.class_id
        $whereSQL
        ORDER BY s.name, r.subject";
$res = $conn->query($sql);

// Organize results per student
$students = [];
while ($row = $res->fetch_assoc()) {
    $sid = $row['student_id'];
    if (!isset($students[$sid])) {
        $students[$sid] = [
            'student_id' => $row['student_id'],
            'name'       => $row['name'],
            'class_name' => $row['class_name'],
            'scores'     => array_fill_keys(array_keys($subjects), null),
            'grandTotal' => 0,
            'count'      => 0,
            'average'    => 0,
        ];
    }
    $students[$sid]['scores'][$row['subject']] = $row['total'];
    if ($row['total'] !== null) {
        $students[$sid]['grandTotal'] += $row['total'];
        $students[$sid]['count']++;
    }
}

// Calculate averages
foreach ($students as &$st) {
    $st['average'] = $st['count'] > 0 ? round($st['grandTotal'] / $st['count'], 2) : 0;
}
unset($st);

// Assign positions
usort($students, fn($a, $b) => $b['grandTotal'] <=> $a['grandTotal']);
$position = 0; $prevTotal = null; $rank = 0;
foreach ($students as &$st) {
    $rank++;
    if ($st['grandTotal'] !== $prevTotal) {
        $position = $rank;
    }
    $st['position'] = $position;
    $prevTotal = $st['grandTotal'];
}
unset($st);

$sessionName = !empty($session) ? $session : 'All_Sessions';
$termName    = !empty($term) ? $term : 'All_Terms';

include 'inc.php';
// ================= BUILD SHEET ===================
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Insert logo
$drawing = new Drawing();
$drawing->setPath('assets/images/delsulogo.jpg');
$drawing->setHeight(80);
$drawing->setCoordinates('E2'); // Positioning the logo in the center top

// Adjust horizontal/vertical offset manually
$drawing->setOffsetX(700); // <-- tweak until logo is centered
$drawing->setOffsetY(10);

$drawing->setWorksheet($sheet);



// School Info Section
$sheet->mergeCells('A7:M7');
$sheet->setCellValue('A7', 'DELSU SECONDARY SCHOOL');
$sheet->getStyle('A7')->getFont()->setBold(true)->setSize(18);
$sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A8:M8');
$sheet->setCellValue('A8', 'P.M.B 1, Abraka, Delta State, Nigeria');
$sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A9:M9');
$sheet->setCellValue('A9', 'BROAD SHEET FOR CONTINUOUS ASSESSMENT');
$sheet->getStyle('A9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A10:M10');
$sheet->setCellValue('A10', "Teacher: $user[full_name]   Class: $className   Term: $termName Term   Session: $sessionName");
$sheet->getStyle('A10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A9')->getFont()->setBold(true);

$sheet->mergeCells('A11:M11');
$sheet->setCellValue('A11', 'Staff ID: ' . $staff_id);
$sheet->getStyle('A11')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Headers
$rowHeader = 13;
$col = 1;

function setCell($sheet, $col, $row, $value) {
    $cell = Coordinate::stringFromColumnIndex($col) . $row;
    $sheet->setCellValue($cell, $value);
}

$headers = ["Student ID", "Name"];
foreach ($headers as $h) {
    setCell($sheet, $col++, $rowHeader, $h);
}

foreach ($subjects as $sub) {
    setCell($sheet, $col++, $rowHeader, $sub);
}

$extraCols = ["Grand Total", "Average", "Position", "Comments/Remarks"];
foreach ($extraCols as $extra) {
    setCell($sheet, $col++, $rowHeader, $extra);
}

// Data rows
$rowIndex = $rowHeader + 1;
foreach ($students as $st) {
    $col = 1;
    setCell($sheet, $col++, $rowIndex, $st['student_id']);
    setCell($sheet, $col++, $rowIndex, $st['name']);
    foreach ($subjects as $subId => $subName) {
        setCell($sheet, $col++, $rowIndex, $st['scores'][$subId] ?? "-");
    }
    setCell($sheet, $col++, $rowIndex, $st['grandTotal']);
    setCell($sheet, $col++, $rowIndex, $st['average']);
    setCell($sheet, $col++, $rowIndex, $st['position']);
    setCell($sheet, $col++, $rowIndex, ""); // remarks placeholder
    $rowIndex++;
}

// Auto-size all columns
foreach (range(1, $col) as $c) {
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
}

// Bold + centered headers
$headerRange = "A{$rowHeader}:" . Coordinate::stringFromColumnIndex($col-1) . "{$rowHeader}";
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
        'startColor' => ['argb' => 'FFDCE6F1'],
    ],
]);

// Borders for data rows
$dataRange = "A" . ($rowHeader+1) . ":" . Coordinate::stringFromColumnIndex($col-1) . ($rowIndex-1);
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
$sheet->freezePane("A" . ($rowHeader+1));

// Protect the sheet, unlock remarks column
$sheet->getProtection()->setSheet(true);
$sheet->getProtection()->setPassword('Password');

$remarksCol = Coordinate::stringFromColumnIndex($col - 1);
$lastRow = $rowIndex - 1;
$remarksRange = "{$remarksCol}" . ($rowHeader+1) . ":{$remarksCol}{$lastRow}";

$sheet->getStyle($remarksRange)
      ->getProtection()
      ->setLocked(Protection::PROTECTION_UNPROTECTED);


// ================= OUTPUT =================
$filename = "Broad_Sheet_For_{$className}_{$sessionName}_Session_{$termName}_Term";
$filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);

ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\".xlsx");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
