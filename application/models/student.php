
<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/** The model that handles students -- nothing but a username to fullname
 *  map.
 */


class Student extends CI_Model {
    public $id;
    public $username;
    public $name;       // Full name

    public function __construct() {
        $this->load->database();
    }

    public function load($username)
    {
        $query = $this->db->get_where('students', array('username' => $username));
        if ($query->num_rows() != 1) {
            $this->id = 0;
            return 0;
        }
        $row = $query->row();
        $this->id = $row->id;
        $this->username = $row->username;
        $this->name = $row->name;
        return $this->id;
    }

    public function loadById($studentId)
    {
        $query = $this->db->get_where('students', array('id' => $studentId));
        if ($query->num_rows() != 1) {
            $this->id = 0;
            return 0;
        }
        $row = $query->row();
        $this->id = $studentId;
        $this->username = $row->username;
        $this->name = $row->name;
        return $this->id;
    }

    /** Creates a student if one with the given username doesn't exist.
     *  Returns the studentId (database row id).
     *  If the username already exists, their id is returned but nothing
     *  else happens unless the names don't match, in which case the system
     *  dies horribly.
     * @param string $username
     * @param string $name
     */
    public function create($username, $name) {
        $this->load($username);
        if ($this->id != 0) {
            if ($this->name != $name) {
                die("Oops. A student already exists with username $username " .
                        "but a different full name ({$this->name} not $name).");
            }
        }
        else {
            $this->db->insert('students', array(
                'username'  => $username,
                'name'      => $name));
            $this->username = $username;
            $this->name = $name;
            $this->id = $this->db->insert_id();
        }
        return $this->id;
    }
}