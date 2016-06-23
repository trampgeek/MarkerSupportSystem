<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * This is a pseudo-model, used to support a hacky mechanism that uploads
 * a spreadsheet of marks (done externally) into this system so it looks like
 * it was marked by it. Used just to display results to table.
 * See the admin/uploadmarks command. Not intended for general use.
 */


class ExternalGradeTable extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function upload($assignmentId, $filename, $models)
    {
        $error = '';
        $correctnessOnly = FALSE;
        $studentModel = $models['student'];
        $marksheetModel = $models['marksheet'];
        $markitemsModel = $models['markitems'];

        if (($fd = fopen($filename, 'r')) == FALSE) {
            $error = "Something went wrong. File couldn't be read.";
        }
        elseif (($markItemIds = fgetcsv($fd)) == FALSE) {
            $error = "No data in file!";
        }
        else {
            // Process the first row, containing the internal ids of mark items
            // in columns 2 (0-origin) on
            array_shift($markItemIds);
            array_shift($markItemIds);
            $numMarkItems = 0;
            foreach ($markItemIds as $markItemId) {
                if ($markItemId != 0) {
                    $numMarkItems++;
                    $markitemsModel->loadById($markItemId); // Will die if bad mark item
                }
            }
            fgetcsv($fd);  // Skipping weight row of spreadsheet
        }

        if ($error == '') {
            $rowNum = 0;
            while (($row = fgetcsv($fd)) !== FALSE) {
                $rowNum++;
                $username = $row[0];
                $studentId = $studentModel->load($username);
                if ($studentId == 0) {
                    $error .= "Student $username not found";
                }
                else {
                    $marksheetId = $marksheetModel->load($assignmentId, $studentId);
                    if ($marksheetId == 0) {
                        // Create marksheet, marked by markerid = 1 (who had better be the admin)
                        $marksheetId = $marksheetModel->create($assignmentId, $studentId, "", 1);
                    }
                    $markitemsModel->clearMarksheetMarkItems($marksheetId);
                    for ($i = 0; $i < $numMarkItems; $i++) {
                        $markItemId = $markItemIds[$i];
                        $markitemsModel->insertMarksheetItem($marksheetId, $markItemId, $row[$i + 2]);
                    }
                    $total = $row[$numMarkItems + 2];
                    $marksheetModel->markTotal = $total;
		    $marksheetModel->markerId = 1;
                    $marksheetModel->update();
                }
            }
        }
        return $error;
    }
}




