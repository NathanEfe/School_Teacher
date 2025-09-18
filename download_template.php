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
    $class_id   = $_POST['class_id'] ?? null;
    $session    = $_POST['session'] ?? '';
    $term       = $_POST['term'] ?? '';
    $subject_id = $_POST['subject_id'] ?? null;
    $staff_id   = $_SESSION['staff_id'];

    if (!$class_id || !$subject_id) {
        die("Class or Subject not provided.");
    }

    // Fetch class name
    $classRes = $conn->query("SELECT class_name FROM classes WHERE class_id='" . $conn->real_escape_string($class_id) . "'");
    $classRow = $classRes->fetch_assoc();
    $className = $classRow['class_name'] ?? '';

    // Fetch selected subject
    $subjectRes = $conn->query("SELECT subject_name FROM jss2_subjects WHERE id='" . $conn->real_escape_string($subject_id) . "'");
    $subjectRow = $subjectRes->fetch_assoc();
    $subjectName = $subjectRow['subject_name'] ?? '';

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
    $sheet->setCellValue('A9', "Class: $className   Term: $term   Session: $session   Subject: $subjectName");
    $sheet->getStyle('A9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A9')->getFont()->setBold(true);

    $sheet->mergeCells('A10:H10');
    $sheet->setCellValue('A10', 'Staff ID: ' . $staff_id);
    $sheet->getStyle('A10')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Table headers
    $headerRow1 = 13; // Subject heading
    $headerRow2 = 14; // CA/Exam/Total/Grade row

    // Fixed headers
    $sheet->setCellValue("A{$headerRow1}", 'Student ID');
    $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");
    $sheet->getStyle("A{$headerRow1}")->getFont()->setBold(true);


    //Apply border top to Student ID
    $sheet->getStyle("A{$headerRow2}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK);

    //Apply border bottom to Student ID
    $sheet->getStyle("A{$headerRow2}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);

        //Apply border left to Student ID
    $sheet->getStyle("A{$headerRow2}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);

        //Apply border right to Student ID
    $sheet->getStyle("A{$headerRow2}")->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);




    $sheet->setCellValue("B{$headerRow1}", 'Student Name');
    $sheet->mergeCells("B{$headerRow1}:B{$headerRow2}");
    $sheet->getStyle("B{$headerRow1}")->getFont()->setBold(true);

    //Apply border top to Student Name
    $sheet->getStyle("B{$headerRow2}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK);

    //Apply border bottom to Student Name
    $sheet->getStyle("B{$headerRow2}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);

        //Apply border left to Student Name
    $sheet->getStyle("B{$headerRow2}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);

        //Apply border right to Student Name
    $sheet->getStyle("B{$headerRow2}")->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);



    // Subject header (C-G)
    $sheet->mergeCells("C{$headerRow1}:G{$headerRow1}");
    $sheet->setCellValue("C{$headerRow1}", $subjectName);
    $sheet->getStyle("C{$headerRow1}")->getFont()->setBold(true);
    $sheet->getStyle("C{$headerRow1}:G{$headerRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    //header color and border
        $sheet->getStyle("C{$headerRow1}:G{$headerRow1}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK)->setColor(new Color('FF000000'));

        $sheet->getStyle("C{$headerRow1}:G{$headerRow1}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF87CEEB'); 

        $sheet->getStyle("C{$headerRow1}:G{$headerRow1}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);


    // Sub-columns
    $sheet->setCellValue("C{$headerRow2}", '1st CA(20)');
    $sheet->setCellValue("D{$headerRow2}", '2nd CA(20)');
    $sheet->setCellValue("E{$headerRow2}", 'Exam(60)');
    $sheet->setCellValue("F{$headerRow2}", 'Total');
    $sheet->setCellValue("G{$headerRow2}", 'Grade');

    $sheet->getStyle("C{$headerRow2}:G{$headerRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("C{$headerRow2}:G{$headerRow2}")->getFont()->setBold(true);
    $sheet->getStyle("C{$headerRow2}:G{$headerRow2}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK)->setColor(new Color('FF000000'));
    $sheet->getStyle("C{$headerRow2}:G{$headerRow2}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF91DDFC');


            //Apply border bottom to all subcolumns
        $sheet->getStyle("C{$headerRow2}:G{$headerRow2}")
            ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);



    // Student rows
    $row = 15;
    while ($stu = $students->fetch_assoc()) {
        $sheet->setCellValue("A{$row}", $stu['student_id']);
        $sheet->setCellValue("B{$row}", $stu['name']);

        // Formulas
        $sheet->setCellValue("F{$row}", "=SUM(C{$row}:E{$row})");
        $sheet->setCellValue("G{$row}", "=IF(F{$row}>=80,\"Distinction\",IF(F{$row}>=70,\"Very Good\",IF(F{$row}>=60,\"Good\",IF(F{$row}>=40,\"Pass\",\"Fail\"))))");

        $row++;
    }
    $rowCount = $row - 1;

            // Apply right border to last column in subject
        $sheet->getStyle("G{$headerRow2}:G{$rowCount}")
            ->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);

            //Apply border bottom to first column in subject
            $sheet->getStyle("C{$headerRow2}:C{$rowCount}")
                ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);

            //Apply border right to first column in subject
            $sheet->getStyle("C{$headerRow2}:C{$rowCount}")
                ->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);

            //Apply border bottom to second column in subject
            $sheet->getStyle("D{$headerRow2}:D{$rowCount}")
                ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);

            //Apply border right to second column in subject
            $sheet->getStyle("D{$headerRow2}:D{$rowCount}")
                ->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);

            //Apply border bottom to third column in subject
            $sheet->getStyle("E{$headerRow2}:E{$rowCount}")
                ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);

            
            //Apply border right to third column in subject
            $sheet->getStyle("E{$headerRow2}:E{$rowCount}")
                ->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);

            //Apply border bottom to fourth column in subject
            $sheet->getStyle("F{$headerRow2}:F{$rowCount}")
                ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);


            //Apply border right to fourth column in subject
            $sheet->getStyle("F{$headerRow2}:F{$rowCount}")
                ->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);

            //Apply border bottom to fifth column in subject
            $sheet->getStyle("G{$headerRow2}:G{$rowCount}")
                ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);

    
        // Add vertical border between Student ID and Student Name
        $sheet->getStyle("A{$headerRow1}:A{$rowCount}")
        ->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);

        // Add vertical border between Student Name and Subject
        $sheet->getStyle("B{$headerRow1}:B{$rowCount}")
        ->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);

        //Add Horizontal border top for Student ID
         $sheet->getStyle("A{$headerRow1}:A{$rowCount}")
        ->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK);

        //Add Horizontal border bottom for Student ID
         $sheet->getStyle("A{$headerRow1}:A{$rowCount}")
        ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);


        //Add Horizontal border top for Student Name
         $sheet->getStyle("B{$headerRow1}:B{$rowCount}")
        ->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK);

        //Add Horizontal border bottom for Student Name
         $sheet->getStyle("B{$headerRow1}:B{$rowCount}")
        ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);


                



    // Protect the sheet
    $sheet->getProtection()->setSheet(true)->setPassword('Password');

    // Unlock only CA1, CA2, Exam
    foreach (['C','D','E'] as $col) {
        $sheet->getStyle("{$col}15:{$col}{$rowCount}")
              ->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
    }

    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(18);
    $sheet->getColumnDimension('B')->setWidth(30);
    foreach (['C','D','E','F','G'] as $col) {
        $sheet->getColumnDimension($col)->setWidth(15);
    }

    // Add validation
    for ($r = 15; $r <= $rowCount; $r++) {
        // CA1, CA2 <= 20
        foreach (['C','D'] as $col) {
            $validation = new DataValidation();
            $validation->setType(DataValidation::TYPE_WHOLE);
            $validation->setOperator(DataValidation::OPERATOR_LESSTHANOREQUAL);
            $validation->setFormula1(20);
            $validation->setAllowBlank(true);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Invalid Input');
            $validation->setError('The value must be a whole number less than or equal to 20 (No decimal)');
            $sheet->getCell("{$col}{$r}")->setDataValidation($validation);
        }
        // Exam <= 60
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_WHOLE);
        $validation->setOperator(DataValidation::OPERATOR_LESSTHANOREQUAL);
        $validation->setFormula1(60);
        $validation->setAllowBlank(true);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Invalid Input');
        $validation->setError('The value must be a whole number less than or equal to 60 (No decimal)');
        $sheet->getCell("E{$r}")->setDataValidation($validation);
    }



    // Output file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename={$className}_Result_Sheet_{$subjectName}_{$term}_Term_{$session}_Session.xlsx");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>
