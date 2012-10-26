
<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/** The model that handles student marksheets.
 */


class Marksheet extends CI_Model {
    public $id;
    public $assignmentId;
    public $studentId;
    public $markerId;
    public $extraInfo;
    public $comments;
    public $bonus;
    public $markTotal;  // Value computed when this sheet is saved (invalid if asst params change)


    public function __construct() {
        $this->load->database();
    }


    public function create($assignmentId, $studentId, $extraInfo) {
        $this->db->insert('mark_sheets', array(
            'assignmentId' => $assignmentId,
            'studentId'    => $studentId,
            'extraInfo'    => $extraInfo
        ));
        $id = $this->db->insert_id();
        if ($id == 0) {
            die("Database error: failed to create marksheet");
        }
        return $id;

    }

    /** Load the marksheet with the given id. Returns the marksheetId or
     *  0 if not such marksheet exists.
     */
    public function loadById($marksheetId) {
        $where = array('id' => $marksheetId);
        return $this->_load($where);
    }

    /** Load the current marksheet from the database for the given studentId
     *  and the given assignment. Returns the marksheetId or 0 if no such
     *  marksheet exists.
     */
    public function load($assignmentId, $studentId)
    {
        $where = array(
                    'assignmentId' => $assignmentId,
                    'studentId'    => $studentId);
        return $this->_load($where);
    }

    // Generalised load, given a 'where' set (which is enhanced by the
    // addition of deleted = 0). Returns the marksheetId or 0 if no such
    // marksheet found.
    private function _load($where) {
        $where['deleted'] = 0;
        $this->db->select(array(
                    'mark_sheets.id as id',
                    'assignmentId',
                    'studentId',
                    'markerId',
                    'extraInfo',
                    'comments',
                    'bonus',
                    'markTotal'))
                ->from('mark_sheets')
                ->where($where);
        $query = $this->db->get();
        if ($query->num_rows() == 0) {
            $this->id = 0;
        }
        elseif ($query->num_rows() > 1) {
            die("Database error. More than one matching marksheet found");
        }
        else {
            foreach($query->row_array() as $key=>$value) {
                $this->$key = $value;
            }
        }
        return $this->id;
    }


    /** Save the current mark sheet to the database. */

    public function update()
    {
        if ($this->id == 0) die("Attempting to update a nonexistent mark sheet");
        $this->db->where('id', $this->id)->update('mark_sheets', array(
            'markerId' => $this->markerId,
            'extraInfo'=> $this->extraInfo,
            'comments' => $this->comments,
            'bonus'    => $this->bonus,
            'markTotal'=> $this->markTotal
        ));
    }



    /** Get a list of all marksheets for all students for a given assignment
     *  Returns a query result, so each item is a stdClass object,
     *  not a marksheet object. */
    public function getAllMarksheets($assignmentId, $sortBy = 'username')
    {
        $this->db->order_by($sortBy);
        $this->db->select('mark_sheets.id as id, markerId, markTotal,' .
                          'students.username as username, studentId, '.
                          'name, markers.username as marker, bonus')
                 ->from('mark_sheets')
                 ->join('students', 'studentId = students.id')
                 ->join('markers', 'markerId = markers.id', 'left')
                 ->where(array('mark_sheets.assignmentId' => $assignmentId,
                      'deleted'      => 0))
                 ->order_by($sortBy);
        $query = $this->db->get();
        return $query->result();
    }

}




