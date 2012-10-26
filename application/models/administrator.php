<?php
class Administrator extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function getAll() {
        $query = $this->db->get('administrators');
        $result = array();
        foreach ($query->result() as $row) {
            $result[] = $row->username;
        }
        return $result;
    }

}

?>
