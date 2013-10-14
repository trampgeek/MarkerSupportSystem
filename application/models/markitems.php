<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/** The model that handles mark items associated with marking of
 *  assignments.
 */
class Markitems extends CI_Model {

    public $id;
    public $assignmentId;
    public $categoryId;
    public $markerId;
    public $persistent;
    public $comment;
    public $mark;

    public function __construct() {
        $this->load->database();
    }

    /** Load this with the specified database row */
    public function loadById($id) {
        $query = $this->db->get_where('mark_items', array(
            'id' => $id,
            'deleted' => 0
        ));
        if ($query->num_rows() != 1) {
            die("Markitems::loadById: non-existent id ($id)");
        }
        foreach ($query->row_array() as $key => $value) {
            $this->$key = $value;
        }
    }


    /**
     * Write this object back to the database
     */
    public function update() {
        $this->db->where('id', $this->id);
        $this->db->update('mark_items', $this);
    }


    /** Add a new mark item relating to a given assignment. Return its id. */
    public function insertItem($assignmentId, $catId, $comment, $mark,
                                $markerId = 0, $persistent = 1) {
        $this->db->insert('mark_items', array(
            'assignmentId'  =>  $assignmentId,
            'categoryId'    =>  $catId,
            'markerId'      =>  $markerId,
            'persistent'    =>  $persistent,
            'comment'       =>  $comment,
            'mark'          =>  $mark
            )
        );
        return $this->db->insert_id();
    }

    /**
     * Mark the given markitem as non-persistent.
     * @param type $id The markitem id to have its persistent flag cleared
     */
    public function discontinue($id) {
        $this->loadById($id);
        $this->persistent = 0;
        $this->update();
    }


    public function insertMarksheetItem($marksheetId, $markItemId, $weight,
            $commentOverride = NULL) {
        $data = array(
            'marksheetId' => $marksheetId,
            'markitemId' => $markItemId,
            'weight'     => $weight
        );
        if ($commentOverride) {
            $data['commentOverride'] = $commentOverride;
        }
        $this->db->insert('marksheet_mark_items', $data);
        return $this->db->insert_id();
    }


    /** Get a list of all the mark items for this assignment and marksheet in the
     *  given category.  Returns a query result on the markItems table.
     */
    public function getMarkItems($assignmentId, $marksheetId, $categoryId, $markerId)
    {
        // Need a union of all the generic mark items on this assignment and
        // all the ones actually referenced (because of non-peristent one-off
        // markitems).
        // Active Record doesn't support union, so have to do it manually :-(


        // First get the generic (for this marker) mark items for the asst
        assert($markerId != 0);
        $this->db->select('*')
                 ->from('mark_items')
                 ->where(array(
                    'assignmentId' => $assignmentId,
                    'categoryId'   => $categoryId,
                    'persistent'   => 1,
                    'deleted'      => 0))
                 ->where("(markerId = 0 OR markerId = $markerId)")
                 ->order_by('mark_items.id');

        $query = $this->db->get();
        $results = array();
        foreach ($query->result() as $row) {
            $results[$row->id] = $row;
        }

        // Now get all the referenced mark items for this marksheet
        $this->db->select('mark_items.id, mark_items.markerId, comment, mark')
                 ->from('mark_items')
                 ->join('marksheet_mark_items', 'markItemId = mark_items.id')
                 ->where(array(
                     'marksheetId'                  => $marksheetId,
                     'categoryId'                   => $categoryId,
                     'marksheet_mark_items.deleted' => 0));
        $query = $this->db->get();

        // Merge them.
        foreach ($query->result() as $row) {
            $results[$row->id] = $row;
        }

        return $results;
    }


    /** Get a list of all the mark items currently selected for a given marksheet
     *  Returns an associative array mapping markitem id to the row with that
     *  id.
     */
    public function getSelectedItems($marksheetId) {
        $this->db->select('mark_items.id as markItemId,
                           mark, markerId, weight, comment, commentOverride')
                 ->from('marksheet_mark_items')
                 ->join('mark_items', 'markItemId=mark_items.id')
                 ->where(array(
                    'markSheetId'                   => $marksheetId,
                    'marksheet_mark_items.deleted'  => 0
                    ));
        $query = $this->db->get();
        $result = array();
        foreach ($query->result() as $item) {
            $result[$item->markItemId] = $item;
        }
        return $result;
    }


    /** Get a list of all the mark items currently selected for a given marksheet
     *  in the specified category.
     *  Returns an associative array of all matching rows.
     */
    public function getSelectedItemsInCategory($marksheetId, $categoryId) {
        $this->db ->select('*')
                  ->from('marksheet_mark_items')
                  ->join('mark_items', 'markItemId = mark_items.id')
                  ->where(array(
                        'markSheetId'                   => $marksheetId,
                        'marksheet_mark_items.deleted'  => 0,
                        'categoryId'                    => $categoryId)
                    );
        $query = $this->db->get();
        $data = array();
        foreach ($query->result() as $row) {
            $data[] = $row;
        }
        return $data;
    }


    /** Return a count of the number of mark items that have been used already
     *  on a given assignment. Used as a check on whether or not marking has
     *  started.
     */
    public function numMarkitems($assignmentId) {
        $this->db->select('marksheet_mark_items.id')
                 ->from('marksheet_mark_items')
                 ->join('mark_items', 'marksheet_mark_items.markItemId = mark_items.id')
                 ->where('assignmentId', $assignmentId);
        $query = $this->db->get();
        return $query->num_rows();
    }


    /** Delete all the mark items for a given assignment
     */
    public function deleteAllMarkitems($assignmentId) {
        // This function is called when uploading fresh mark items
        // to an assignment. There shouldn't be any existing
        // marksheet->markitem links, so these aren't deleted.

        $this->db->where(array('assignmentId' => $assignmentId));
        $this->db->update('mark_items', array('deleted'=>1));
    }


    /** Delete all marksheet items for the given marksheet.
     *
     * @param int $marksheetId
     */
    public function clearMarksheetMarkItems($marksheetId) {
        $this->db->where(array('marksheetId' => $marksheetId));
        $this->db->update('marksheet_mark_items', array('deleted' => 1));
    }

}
