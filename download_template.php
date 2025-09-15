<?php
require 'db_connect.php';
require 'vendor/autoload.php'; // PhpSpreadsheet autoloader
session_start();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'] ?? null;
    $session  = $_POST['session'] ?? '';
    $term     = $_POST['term'] ?? '';
    $staff_id = $_SESSION['staff_id'];

    if (!$class_id) {
        die("Class not provided.");
    }

    // Fetch class name
    $classRes = $conn->query("SELECT class_name FROM classes WHERE class_id='" . $conn->real_escape_string($class_id) . "'");
    $classRow = $classRes->fetch_assoc();
    $className = $classRow['class_name'] ?? '';

    // Fetch ALL subjects
    $subjectsRes = $conn->query("SELECT id, subject_name FROM jss2_subjects ORDER BY subject_name ASC");
    $subjects = [];
    while ($s = $subjectsRes->fetch_assoc()) {
        $subjects[] = $s;
    }

    // Fetch students
    $students = $conn->query("SELECT student_id, name FROM jss2_students_records WHERE class_id='" . $conn->real_escape_string($class_id) . "' ORDER BY student_id ASC");

    // Spreadsheet init
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

    // Table headers
    $headerRow1 = 13; // Subject headings row
    $headerRow2 = 14; // CA/Exam/Total row


    // Fixed headers
    $sheet->setCellValue("A{$headerRow1}", 'Student ID');
    $sheet->getStyle("A{$headerRow1}")->getFont()->setBold(true);
    $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");

    $sheet->setCellValue("B{$headerRow1}", 'Student Name');
    $sheet->getStyle("B{$headerRow1}")->getFont()->setBold(true);
    $sheet->mergeCells("B{$headerRow1}:B{$headerRow2}");

    $colIndex = 3; // start from column C
    foreach ($subjects as $subject) {
        // Subject heading (merged across 4 columns)
        $colStart = Coordinate::stringFromColumnIndex($colIndex);
        $colEnd   = Coordinate::stringFromColumnIndex($colIndex + 4);

        $sheet->mergeCells("{$colStart}{$headerRow1}:{$colEnd}{$headerRow1}");
        $sheet->setCellValue("{$colStart}{$headerRow1}", $subject['subject_name']);
        $sheet->getStyle("{$colStart}{$headerRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$colStart}{$headerRow1}")->getFont()->setBold(true);
        $sheet->getStyle("{$colStart}{$headerRow1}:{$colEnd}{$headerRow1}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK)->setColor(new Color('FF0000FF'));

        $sheet->getStyle("{$colStart}{$headerRow1}:{$colEnd}{$headerRow1}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF87CEEB'); 


        // Sub-columns (CA1, CA2, Exam, Total, Grade)
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex)     . $headerRow2, '1st CA(20)');
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex + 1) . $headerRow2, '2nd CA(20)');
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex + 2) . $headerRow2, 'Exam(60)');
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex + 3) . $headerRow2, 'Total');
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex + 4) . $headerRow2, 'Grade');

        // Apply bold font to those cells
        $sheet->getStyle(Coordinate::stringFromColumnIndex($colIndex) . $headerRow2 . ':' . Coordinate::stringFromColumnIndex($colIndex + 4) . $headerRow2)
        ->getFont()->setBold(true);

        $colIndex += 5;
    }

    // Student rows
    $row = 15;
    while ($stu = $students->fetch_assoc()) {
        $sheet->setCellValue("A{$row}", $stu['student_id']);
        $sheet->setCellValue("B{$row}", $stu['name']);
        $rowCount = $row - 1; // define it

        $colIndex = 3;
        foreach ($subjects as $subject) {
            $ca1   = Coordinate::stringFromColumnIndex($colIndex);
            $ca2   = Coordinate::stringFromColumnIndex($colIndex+1);
            $exam  = Coordinate::stringFromColumnIndex($colIndex+2);
            $total = Coordinate::stringFromColumnIndex($colIndex+3);
            $grade = Coordinate::stringFromColumnIndex($colIndex+4);

            // Total formula
            $sheet->setCellValue("{$total}{$row}", "=SUM({$ca1}{$row}:{$exam}{$row})");

            // Grade formula
            $sheet->setCellValue("{$grade}{$row}", "=IF({$total}{$row}>=80,\"Distinction\",IF({$total}{$row}>=70,\"Very Good\",IF({$total}{$row}>=60,\"Good\",IF({$total}{$row}>=40,\"Pass\",IF({$total}{$row}>=0,\"Fail\",\"Invalid\")))))");

            // Hide formulas for this subject group
            // $sheet->getStyle("{$total}15:{$total}{$rowCount}")
            //     ->getProtection()->setLocked(Protection::PROTECTION_PROTECTED)
            //     ->setHidden(true);

            $sheet->getStyle("{$grade}15:{$grade}{$rowCount}")
                ->getProtection()->setLocked(Protection::PROTECTION_PROTECTED)
                ->setHidden(true);

            $colIndex += 5;
        }

        $row++;
    }

    $rowCount = $row - 1;




    // Protect the entire sheet
    $sheet->getProtection()->setSheet(true);
    $sheet->getProtection()->setPassword('Password');

    
    // Hide formulas in Total and Grade columns
    $colIndex = 3;
    foreach ($subjects as $subject) {
        $totalCol = Coordinate::stringFromColumnIndex($colIndex + 3);
        $gradeCol = Coordinate::stringFromColumnIndex($colIndex + 4);

        $sheet->getStyle("{$totalCol}15:{$totalCol}{$rowCount}")
            ->getProtection()
            ->setLocked(Protection::PROTECTION_PROTECTED)
            ->setHidden(true);

        $sheet->getStyle("{$gradeCol}15:{$gradeCol}{$rowCount}")
            ->getProtection()
            ->setLocked(Protection::PROTECTION_PROTECTED)
            ->setHidden(true);

        $colIndex += 5;
    }

    // Unlock only CA1, CA2, Exam
    $colIndex = 3;
    foreach ($subjects as $subject) {
        $caCols = [
            Coordinate::stringFromColumnIndex($colIndex),
            Coordinate::stringFromColumnIndex($colIndex + 1),
            Coordinate::stringFromColumnIndex($colIndex + 2)
        ];
        foreach ($caCols as $c) {
            $sheet->getStyle("{$c}15:{$c}{$rowCount}")
                ->getProtection()
                ->setLocked(Protection::PROTECTION_UNPROTECTED);
        }
        $colIndex += 5;
    }

    // Unlock CA/Exam columns
    // $colIndex = 3;
    // foreach ($subjects as $subject) {
    //     $cols = [
    //         Coordinate::stringFromColumnIndex($colIndex),
    //         Coordinate::stringFromColumnIndex($colIndex+1),
    //         Coordinate::stringFromColumnIndex($colIndex+2)
    //     ];
    //     foreach ($cols as $col) {
    //         $sheet->getStyle("{$col}15:{$col}{$rowCount}")
    //             ->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
    //     }
    //     $colIndex += 5; // FIXED
    // }

    // Set width for Student ID and Student Name columns
    $sheet->getColumnDimension('A')->setWidth(18); // Student ID
    $sheet->getColumnDimension('B')->setWidth(30); // Student Name



    //Increase the width for other columns
    $colIndex = 3;
    foreach ($subjects as $subject) {
        $ca1Col   = Coordinate::stringFromColumnIndex($colIndex);
        $ca2Col   = Coordinate::stringFromColumnIndex($colIndex + 1);
        $examCol  = Coordinate::stringFromColumnIndex($colIndex + 2);
        $totalCol = Coordinate::stringFromColumnIndex($colIndex + 3);
        $gradeCol = Coordinate::stringFromColumnIndex($colIndex + 4);

        //Set width
        $sheet->getColumnDimension($ca1Col)->setWidth(12);
        $sheet->getColumnDimension($ca2Col)->setWidth(12);
        $sheet->getColumnDimension($examCol)->setWidth(12);
        $sheet->getColumnDimension($totalCol)->setWidth(14);
        $sheet->getColumnDimension($gradeCol)->setWidth(18);



        $colIndex += 5;
    }


    // Add validation
    for ($r = 15; $r <= $rowCount; $r++) {
    $colIndex = 3;
    foreach ($subjects as $subject) {
        $ca1 = Coordinate::stringFromColumnIndex($colIndex);
        $ca2 = Coordinate::stringFromColumnIndex($colIndex+1);
        $exam = Coordinate::stringFromColumnIndex($colIndex+2);

        // CAs <=20
        foreach ([$ca1, $ca2] as $c) {
            $validation = new DataValidation();
            $validation->setType(DataValidation::TYPE_WHOLE);
            $validation->setOperator(DataValidation::OPERATOR_LESSTHANOREQUAL);
            $validation->setFormula1(20);
            $validation->setAllowBlank(true);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Invalid Input');
            $validation->setError('The value must be lesser than or equal to 20.');
            $sheet->getCell($c.$r)->setDataValidation($validation);
        }

        // Exam <=60
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_WHOLE);
        $validation->setOperator(DataValidation::OPERATOR_LESSTHANOREQUAL);
        $validation->setFormula1(60);
        $validation->setAllowBlank(true);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Invalid Input');
        $validation->setError('The value must be lesser than or equal to 60.');
        $sheet->getCell($exam.$r)->setDataValidation($validation);

        $colIndex += 5; // FIXED
    }
    }

        // Auto-size columns
        // for ($i = 1; $i < $colIndex; $i++) {
        //     $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        // }

        // Set width for each Grade column (every 5th column after column C)
        $colIndex = 3;
        foreach ($subjects as $subject) {
            $gradeColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 4); // Grade column
            $sheet->getColumnDimension($gradeColLetter)->setWidth(18); // or whatever width you want
            $colIndex += 5; // Move to next subject group
        }


    // Output file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=Result_Sheet_{$className}_{$term}_Term_{$session}_Session.xlsx");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>
