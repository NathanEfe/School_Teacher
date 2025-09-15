<?php
require 'db_connect.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// ================= EXPORT TO EXCEL ===================
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Headers
    $sheet->fromArray(
        ['Student ID','Name','Class','Subject','Term','Session','1st CA','2nd CA','Exam','Total'],
        NULL,
        'A1'
    );

    $sql = "SELECT r.student_id, s.name, c.class_name, sub.subject_name, r.term, r.session,
                   r.first_ca, r.second_ca, r.exam, r.total
            FROM results r
            JOIN jss2_students_records s ON r.student_id = s.student_id
            JOIN classes c ON r.class = c.class_id
            JOIN jss2_subjects sub ON r.subject = sub.id
            $whereSQL
            ORDER BY c.class_name, sub.subject_name, s.name";
    $res = $conn->query($sql);

    $row = 2;
    while ($data = $res->fetch_assoc()) {
        $sheet->fromArray([
            $data['student_id'],
            $data['name'],
            $data['class_name'],
            $data['subject_name'],
            $data['term'],
            $data['session'],
            $data['first_ca'],
            $data['second_ca'],
            $data['exam'],
            $data['total']
        ], NULL, "A$row");
        $row++;
    }

    // Clean buffer before output
    if (ob_get_length()) ob_end_clean();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="results.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
