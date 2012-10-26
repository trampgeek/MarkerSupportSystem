<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/** The model that handles a studentlist spreadsheet, used to load all
 *  the students for a given course into the database. Creates
 *  a new entry in the students table if necessary and a new marksheet
 *  if necessary. If a student with the given username already exists but
 *  has a different name, the system dies. If the marksheet already exists,
 *  only the extrainfo field is altered.
 */

class StudentlistSpreadsheet extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function upload($assignmentId, $filename, $models)
    {
        $error = '';
        $correctnessOnly = FALSE;
        $studentModel = $models['student'];
        $marksheetModel = $models['marksheet'];

        if (($fd = fopen($filename, 'r')) == FALSE) {
            $error = "Something went wrong. File couldn't be read.";
        }

        if ($error == '') {
            $rowNum = 0;
            while (($row = fgetcsv($fd)) !== FALSE) {
                $rowNum++;
                if (count($row) == 3) {
                    list($username, $name, $extraInfo) = $row;
                }
                elseif (count($row) == 2) {
                    list($username, $name) = $row;
                    $extraInfo = '';
                }
                else {
                    $error = "Wrong number of columns at row $rowNum";
                    break;
                }
                if (strlen($username) > 7) {
                    $error = "Implausible username ($username) at row $rowNum";
                    break;
                }
                if (strlen($name) < 5 || strpos($name, ' ') === FALSE) {
                    $error = "Implausible full name ($name) at row $rowNum";
                    break;
                }

                $studentId = $studentModel->load($username);
                if ($studentId == 0) {
                    $studentId = $studentModel->create($username, $name);
                }
                else if ($studentModel->name != $name) {
                    $error = "Warning: student $username already exists but with different name ({$studentModel->name})";
                }

                $marksheetId = $marksheetModel->load($assignmentId, $studentId);
                if ($marksheetId != 0) {
                    $marksheetModel->extraInfo = $extraInfo;
                    $marksheetModel->update();
                }
                else {
                    $marksheetModel->create($assignmentId, $studentId, $extraInfo);
                }
            }
        }
        return $error;
    }
}




