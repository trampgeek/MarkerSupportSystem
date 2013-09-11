<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/** The model for an assignment, represented in the database
 *  by the single-row of the table Assignments joined with the
 *  markers.
 */


class Assignment extends CI_Model {
    // The set of instance variables must match the table column names
    public $id;
    public $courseCode;
    public $assignmentName;
    public $introduction;
    public $startingMark;
    public $pseudoMaxPenalty;
    public $outOf;
    public $markDisplayToStudent;


    public function __construct() {
        $this->load->database();
    }

    /** Insert a new assignment into the database, returning its id.
     *  Also sets this to the specified record.
     *  Parameter names must match the db column names. */
    public function insert($data) {
        foreach($data as $field => $value) {
            $this->$field = $value;
        }
        $this->db->insert('assignments', $data);
        $this->id = $this->db->insert_id();
        return $this->id;
    }

    /** Load the params for the nominated assignment id from the database
     *  into this object.
     */
    public function loadById($id)
    {
        $query = $this->db->get_where('assignments', array('id' => $id));
        if ($query->num_rows() != 1) {
            return False;
        }

        $row = $query->row_array();
        foreach ($row as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        return True;
    }


    /** Return a list of all non-deleted assignments */
    public function getAll() {
        $this->db->order_by('timestamp desc');
        $query = $this->db->get_where('assignments', array('deleted' => 0));
        return $query->result();
    }

    /** Returns an array of all non-deleted assignments created within the
     *  last six months, as an array of id => name mappings. Sorted
     *  most recent first. Names are either just the assignment name (for
     *  use in testing only) or the coursecode, a colon and space and the name
     *  (usually) depending on the parameter.
     */
    public function getAllCurrentAssignments($includeCourseInName = TRUE) {
        $result = array();
        $this->db->order_by('id desc');
        $query = $this->db->get_where('assignments',
                'deleted = 0 AND timestamp > DATE_SUB(NOW(), INTERVAL 6 MONTH)');
        foreach ($query->result() as $row) {
            $name = $row->assignmentName;
            if ($includeCourseInName) {
                $name = $row->courseCode . ': ' . $name;
            }
            $result[$row->id] = $name;
        }
        return $result;
    }

    /** Update the current assignment ($this) on the database.
     */
    public function update()
    {
        $this->db->where('id', $this->id);
        $this->db->update('assignments', $this);
    }


    /** Delete the specified assignment */
    public function delete($id) {
        $this->db->where(array('id'=>$id));
        $this->db->update('assignments', array('deleted' => 1));
    }
}




