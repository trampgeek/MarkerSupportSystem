<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/** The model that handles categories of mark items.
 */


class Categories extends CI_Model
{
    public function __construct() {
        $this->load->database();
    }

    public function addCategory($assignmentId, $category) {
        $this->db->insert('comment_categories',
                    array('assignmentId' => $assignmentId,
                            'category' => $category));
        return $this->db->insert_id();
    }

    /** Get a list of all the mark item categories that apply to a
     *  given assignment. Returns an array of objects each with an id
     *  and a category (i.e., its name or descriptive string).
     *  @param int $assignmentId
     */
    public function getCategories($assignmentId) {
        $this->db->select(array('id', 'category'))
                 ->from('comment_categories')
                 ->where(array(
                     'assignmentId' => $assignmentId,
                     'deleted'      => 0));
        $query = $this->db->get();
        return $query->result();
    }

    /** Delete all categories for a given assignment
     */
    public function deleteAllCategories($assignmentId) {
        $this->db->where(array('assignmentId' => $assignmentId));
        $this->db->update('comment_categories', array('deleted'=>1));
    }
}