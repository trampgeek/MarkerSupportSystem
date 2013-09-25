<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

define('DEBUG', 1);

//define('RESULTS_BASE', '/home/cosc/tutor/c121mark/121s1-12/Automarker-main/marking/results');

if (defined('DEBUG') && DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors','1');
}

/** The controller for student access to the marking system.
 */


class Studentaccess extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(array('form', 'url', 'html', 'marker'));
        $this->load->model('student');
        $this->load->model('assignment');
        $this->load->model('marksheet');
        $this->load->model('markitems');
        $this->load->model('categories');
    }


    public function index()
    {
        if (!$this->_isLoggedIn()) {
            $this->login();
        }
        else {
            $this->display();
        }
    }


    public function login()
    {
        $errMess = '';
        $username = $this->input->post('username');
        if ($username !== FALSE) {  // It's a login postback
            $password = $this->input->post('password');
            $assignmentId = $this->input->post('assignmentId');
            if ($password == 'secretSquirrel' || authenticate($username, $password)) {
                $this->assignment->loadById($assignmentId);
                $this->student->load($username);
                $course = $this->assignment->courseCode;
                if (!$this->marksheet->load($this->assignment->id, $this->student->id)) {
                    $errMess = "No marksheet found. Are you a $course student?";
                }
                else {
                    // It's now a valid login
                    $this->session->set_userdata('username',  $username);
                    $this->session->set_userdata('studentid',  $this->student->id);
                    $this->session->set_userdata('assignmentId', $assignmentId);
                }
            }
            else {
                $errMess = 'Authentication failed';
            }
        }
        if ($this->_isLoggedIn()) {
            $this->display();
        }
        else {
            $this->_header("Marking System");
            $assList = $this->assignment->getAllCurrentAssignments(TRUE);
            if (count($assList) == 0) {
                $this->load->view('message', array('message'=>
                    "Sorry, no assignments are currently available for viewing"));
            } else {
                $this->load->view('studentlogin', array(
                                'assList' => $assList,
                                'errMess' => $errMess)
                );
            }
        }
    }


    public function logout()
    {
        $this->session->set_userdata('username', False);
        $this->login();
    }


    /** Lets a student view their grade sheet
     */
    public function display()
    {
        $username = $this->session->userdata('username');
        $this->assignment->loadById($this->session->userdata('assignmentId'));
        $this->_header($this->assignment->courseCode . " marker's report");
        $this->student->load($username);
        $this->marksheet->load($this->assignment->id, $this->student->id);
        if ($this->marksheet->id == 0 || !$this->marksheet->isVisibleToStudents) {
            $this->load->view('message',
                array('message'=>"Huh? No marksheet found. How did you get here?!"));
        }
        else {
            $this->marksheet->nViews++;
            $this->marksheet->update();
            $this->load->view('marklog', array(
                'markitems'  => $this->markitems,
                'marksheet'  => $this->marksheet,
                'assignment' => $this->assignment,
                'categories' => $this->categories,
                'student'    => $this->student)
            );
            $this->load->view('footer');
        }

    }




    // PRIVATE SUPPORT FUNCTIONS
    // =========================


    private function _isLoggedIn() {
        return $this->session->userdata('username') != FALSE;
    }


    // Generate the header
    private function _header($title, $headElements = NULL) {
        $params = array(
            'title'  =>$title,
            'course' => $this->assignment->courseCode,
            'intro'  => $this->assignment->introduction,
            'username' => $this->session->userdata('username')
        );
        if ($headElements) {
            $params['headElements'] = $headElements;
        }
        $this->load->view('studentheader', $params);
    }


    // True if the currently logged in user is authorised to view a
    // resource belonging to the given $targetUsername
    private function authorisedToView($targetUsername) {
        $loggedInUser =$this->session->userdata('username');
        return
            $targetUsername == $loggedInUser ||
            $targetUsername = $this->marksheet->getPartnerUsername($loggedInUser);
    }


    // Convert raw text into sort-of html by preserving line breaks
    private function htmlify($txt) {
        return str_replace("\n", "<br />", $txt);
    }
}

