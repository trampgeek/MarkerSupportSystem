<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/** The model that handles markers -- nothing but a username to id  map.
 */


class Markers extends CI_Model {
    public $id;
    public $username;

    public function __construct() {
        $this->load->database();
    }


    // Inserts a given marker for a given assignment and returns
    // their id (or, if the marker already exists for this assignment,
    // leaves the database unchanged but returns the id).
    public function insert($assignmentId, $username) {
        $query = $this->db->get_where('markers', array(
            'assignmentId'  => $assignmentId,
            'username'      => $username)
        );
        if ($query->num_rows() != 0) {
            $id = $query->row()->id;
        }
        else {
            $this->db->insert('markers', array(
                'assignmentId'  => $assignmentId,
                'username'      => $username)
            );
            $id = $this->db->insert_id();
        }
        return $id;
    }

    public function insertList($assignmentId, $markerList) {
        foreach($markerList as $markerUsername) {
            $this->insert($assignmentId, $markerUsername);
        }
    }


    public function deleteMarkers($assignmentId) {
        $this->db->where(array('assignmentId' => $assignmentId));
        $this->db->delete('markers');
    }


    /** Return a list of permitted markers for the given assignment.
     */
    public function getMarkers($assignmentId) {
        $this->db->where('assignmentId', $assignmentId);
        $this->db->order_by('id');
        $query = $this->db->get_where('markers', array('assignmentId' => $assignmentId));
        $markers = array();
        foreach ($query->result() as $marker) {
            $markers[] = $marker->username;
        }
        return $markers;
    }


    public function getMarkerId($username, $assignmentId) {
        $query = $this->db->get_where('markers', array(
            'username' => $username,
            'assignmentId' => $assignmentId)
        );
        if ($query->num_rows() > 1) {
            die("Marker::getId multiple occurrences found for marker ($username)");
        }
        else if ($query->num_rows() == 0) {
            die("Marker:getId nonexistent marker ($username)");
        }
        else {
            return $query->row()->id;
        }
    }


    public function getMarkerUsername($markerId) {
        $query = $this->db->get_where('markers', array('id' => $markerId));
        if ($query->num_rows() != 1) {
            return "**ERROR_BAD_MARKER**";
        }
        else {
            return $query->row()->username;
        }
    }


}
