<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Get the DataValidation object for cell A1
$validation = $sheet->getCell('A1')->getDataValidation();

// Set the validation type to 'Whole number'
$validation->setType(DataValidation::TYPE_WHOLE);

// Set the validation operator to 'less than or equal to'
$validation->setOperator(DataValidation::OPERATOR_LESSTHANOREQUAL);

// Set the maximum value
$validation->setFormula1(100);

// Set up the error message for invalid input
$validation->setErrorStyle(DataValidation::STYLE_STOP);
$validation->setShowErrorMessage(true);
$validation->setErrorTitle('Invalid Input');
$validation->setError('The value you entered is greater than 100. Please enter a whole number less than or equal to 100.');

// Set a prompt message to appear when the user clicks the cell
$validation->setShowInputMessage(true);
$validation->setPromptTitle('Enter a value');
$validation->setPrompt('Please enter a whole number between 1 and 100.');

// Add some initial values to other cells
$sheet->setCellValue('A2', 'Some other data');

// Write the Excel file to disk
$writer = new Xlsx($spreadsheet);
$filename = 'excel_with_validation.xlsx';
$writer->save($filename);

echo "Excel file '$filename' created with data validation on cell A1.";
