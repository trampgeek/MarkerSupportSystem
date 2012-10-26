<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/** The model that handles a markItemSpreadsheet, used to load all
 *  the markitems into the database.
 */


class MarkItemSpreadsheet extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function upload($assignmentId, $filename, $models)
    {
        assert($assignmentId != 0);
        $error = '';
        $correctnessOnly = FALSE;
        $studentModel = $models['student'];
        $marksheetModel = $models['marksheet'];
        $markitemModel = $models['markitems'];
        $categoriesModel = $models['categories'];

        if (($fd = fopen($filename, 'r')) == FALSE) {
            $error = "Something went wrong. File couldn't be read.";
        }

        // Make sure no marking has yet been done on this assignment
        if ($markitemModel->numMarkitems($assignmentId) != 0) {
            $error = "Mark item upload is not legal: marking already underway";
        }

        if ($error == '') {
            // First, "delete" all existing mark item info for this asst
            // (which shouldn't include any actual used mark items).
            $markitemModel->deleteAllMarkitems($assignmentId);
            $categoriesModel->deleteAllCategories($assignmentId);

            // Now add the new stuff
            $category = '';
            $rowNum = 0;
            while (($row = fgetcsv($fd)) !== FALSE) {
                $rowNum++;
                if (count($row) < 2 || ($row[0] == '' && $row[1] == '' && $row[2] == '')) {
                    continue; // Ignore blank lines
                }
                if ($row[0] == '' && $category == '') {
                    $error = "First row must have a non-empty category";
                    break;
                }
                if ($row[0] != '') {
                    $newCategory = trim($row[0]);
                    if ($newCategory != $category) {
                        $catId = $categoriesModel->addCategory($assignmentId, $newCategory);
                        $category = $newCategory;
                    }
                }
                if ($row[1] == '' || $row[2] == '' || !is_numeric($row[2])) {
                    $error = "Invalid data at row $rowNum";
                    break;
                }
                $comment = trim($row[1]);
                $mark = floatval($row[2]);
                $markitemModel->insertItem($assignmentId, $catId, $comment, $mark);
            }
        }
        return $error;
    }
}




