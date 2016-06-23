<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/** The controller for the marking system's administrator functionality.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

class Admin extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('assignment');
        $this->load->model('marksheet');
        $this->load->model('student');
        $this->load->model('categories');
        $this->load->model('administrator');
        $this->load->model('markitems');
        $this->load->model('markers');
        $this->load->helper(array('form', 'url', 'html', 'marker', 'date'));
    }

    public function index()
    {
        $errMess = '';
        $username = $this->input->post('username');
        if ($username) {  // Login postback
            $password = $this->input->post('password');
            $admins = $this->administrator->getAll();
            if (count($admins) == 0) {
                die("No administrators?! We're dead, Fred.");
            }
            if (authenticate($username, $password, $admins)) {
                $this->session->set_userdata('adminusername',  $username);
            } else {
                $errMess = 'Authentication failed';
            }
        }

        $this->_header('Assignment Marking System (Admin)');
        if (!$this->_isLoggedIn()) {
            $this->load->view('adminlogin', array(
                              'errMess'   => $errMess)
            );
        }

        else {  // Logged in. Display menu + assignment list
            $this->loadHome();
        }
    }


    public function logout() {
        $this->session->set_userdata('adminusername', '');
        $this->index();
    }


    public function editAssignment($id) {
        if ($id != 0) {
            if (!$this->assignment->loadById($id)) {
                die("Loading a nonexistent assignment? We're dead, Fred");
            }
            $title = "Edit assignment";
        }
        else {
            $title = "New assignment";
        }
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<p class="error">', '</p>');
        $this->form_validation->set_rules('courseCode', 'Course code', 'required');
        $this->form_validation->set_rules('assignmentName', 'Assignment name', 'required');
        $this->form_validation->set_rules('startingMark', 'Starting mark', 'required');
        $this->form_validation->set_rules('startingMark', 'Starting mark', 'numeric');
        $this->form_validation->set_rules('pseudoMaxPenalty', 'Maximum penalty', 'numeric');
        $this->form_validation->set_rules('outOf', "'Mark is out of'", 'required');
        $this->form_validation->set_rules('outOf', "'Mark is out of'", 'numeric');
        $this->form_validation->set_rules('markers', 'Markers', 'required');

        if (!$this->form_validation->run()) {
            $this->_header('Assignment Marking System (Admin)');
            $markers = $id == 0 ? array() : $this->markers->getMarkers($id);
            if ($id) {
                $this->assignment->loadById($id);
            }
            $this->load->view('assignment', array(
                'assignment' => $this->assignment,
                'markers'    => $markers)
            );
        }
        else {
            $markers = $this->extractMarkers($this->input->post('markers'));
            $fields = array('courseCode', 'assignmentName', 'introduction',
                                'startingMark', 'pseudoMaxPenalty', 'outOf',
                                'markDisplayToStudent', 'isVisibleToStudents');
            if ($id == 0) {

                $dataFromForm = array();
                foreach ($fields as $field) {
                    $dataFromForm[$field] = $this->input->post($field);
                }
                if ($dataFromForm['isVisibleToStudents'] == '') {
                    $dataFromForm['isVisibleToStudents'] = 0;  // Unsuccessful checkbox
                }
                $id = $this->assignment->insert($dataFromForm);
                if ($id == 0) {
                    die("Database error: insertion of new assignment failed.");
                }
                $this->markers->insertList($id, $markers);
            }
            else {
                $this->assignment->loadById($id);
                foreach($fields as $field) {
                    $this->assignment->$field = $this->input->post($field);
                }
                $this->assignment->update();
                // $this->markers->deleteMarkers($id); // Mustn't delete as changes marker ids
                $this->markers->insertList($id, $markers);
            }
            $this->_header('Assignment Marking System (Admin)');
            $this->loadHome();
        }
    }



    /** Upload a .csv spreadsheet that lists all the mark items for the given
     *  assignment. WARNING! Discards all existing mark items and student
     *  markitems for this assignment.
     *  The spreadsheet has 3 columns: Category, MarkItemDescription,
     *  Mark. If omitted, the category is assumed to be the same as for
     *  the previous row. Mark is positive for a reward mark item, negative
     *  for a penalty.
     */
    public function uploadMarkitems($assignmentId)
    {
        if (!$this->_isLoggedIn()) {
            $this->doLogin();
            return;
        }
        $this->_uploadSpreadsheet($assignmentId, 'markitemspreadsheet',
                'uploadMarkitems',
                array(
                    'student'   => $this->student,
                    'marksheet' => $this->marksheet,
                    'markitems' => $this->markitems,
                    'categories' => $this->categories)
                );
    }


    /** Delete an assignment (by marking it deleted in the DB) */
    public function delete($id) {
        if (!$this->_isLoggedIn()) {
            $this->doLogin();
            return;
        }
        if ($id == 0) {
            die("Deleting a nonexistent assignment??");
        }
        $this->_header('Assignment Marking System (Admin)');
        if ($this->input->post('confirmDelete')) {
            $this->assignment->delete($id);
            $this->loadHome();
        } else if ($this->input->post('cancelDelete')) {
            $this->loadHome();
        }
        else {
            $this->assignment->loadById($id);
            $this->load->view('confirmDelete', array('assignment' => $this->assignment));
        }
    }


    /** Upload a csv spreadsheet that lists all the students who might
     *  potentially attempt this assignment.
     *  The spreadsheet has 3 columns: usernames, full names,
     *  correctness mark (or any other preliminary mark info -- for marker's
     *  information only). Each student in the list is added to
     *  the database if they're not already there, and a dummy marksheet is created
     *  for them unless one exists already. In the latter case, only the extrainfo
     *  field of the marksheet is updated.
     */
    public function uploadStudents($assignmentId)
    {
        if (!$this->_isLoggedIn()) {
            die("Not logged in.");
        }

        $this->_uploadSpreadsheet($assignmentId,
                'studentlistspreadsheet',
                'uploadStudents',
                array(
                    'student'   => $this->student,
                    'marksheet' => $this->marksheet)
                );
    }


    /**
     * Hacked together method to upload the data from an already-marked
     * assignment into this database, for releasing results to students.
     */
    public function uploadExternalGrades($assignmentId)
    {
        if (!$this->_isLoggedIn()) {
            die("Not logged in.");
        }
        $this->_uploadSpreadsheet($assignmentId,
                'externalgradetable',
                'uploadexternalgradetable',
                array('student'   => $this->student,
                      'marksheet' => $this->marksheet,
                      'markitems' => $this->markitems)
                );

    }


    /** Unit test method just for testing upload of a spreadsheet of students
     *  Used only by unit tester.
     */
    public function testUploadStudents($asstName)
    {
         if (!$this->_isLoggedIn()) {
            die("Not logged in.");
        }
        $filename = '/home/cosc/staff/rjl83/public_html/marking/unittests/AngusAndXin.csv';
        $this->load->model('studentlistspreadsheet');
        $this->testSpreadsheetUpload($asstName, 'studentlistspreadsheet', $filename,
                array('student'   => $this->student,
                      'marksheet' => $this->marksheet
                )
        );
    }


    /** Unit test method just for testing upload of a spreadsheet of assignment
     *  mark items. Used only by unit tester.
     */
    public function testUploadMarkitems($asstName)
    {
        if (!$this->_isLoggedIn()) {
            die("Not logged in.");
        }
        $filename = '/home/cosc/staff/rjl83/public_html/marking/unittests/markitems.csv';
        $this->load->model('markitemspreadsheet');
        $this->testSpreadsheetUpload($asstName, 'markitemspreadsheet', $filename,
                array('student'    => $this->student,
                      'marksheet'  => $this->marksheet,
                      'markitems'  => $this->markitems,
                      'categories' => $this->categories
                )
        );
    }


    public function about()
    {
        $this->_header('About the marking support system');
        $this->load->view('about');
    }


    // PRIVATE/PROTECTED SUPPORT FUNCTIONS
    // ==================================
    //

    /** Extract an array of marker usernames from a comma-separated list */
    private function extractMarkers($s) {
        $bits = explode(',', $s);
        $markers = array();
        foreach ($bits as $bit) { 
            $username = trim($bit);
            if ($username !== '') {
                $markers[] = $username;
            }
        }
        return $markers;
    }


    // Generate the header
    protected function _header($title) {
        $this->load->view('adminheader', array(
            'title'  => $title,
            'username' => $this->session->userdata('adminusername')
       ));
    }

    /** Load the home page for the administrator */
    protected function loadHome() {
        $this->load->model('assignment');
        $assts = $this->assignment->getAll();
        $this->load->view('assignmentsadmin', array('assignments'=>$assts));
    }


    protected function _isLoggedIn() {
        return $this->session->userdata('adminusername') != '';
    }


    /** Upload a spreadsheet of data to a given model from a given view.
     *
     * @param int $assignmentId
     * @param Model $model
     * @param View $view
     * @param assoc array $params (spreadsneet specific).
     */
    private function _uploadSpreadsheet($assignmentId, $model,
                                        $view, $params = NULL)
    {
        assert($assignmentId != 0);
        if (!$this->_isLoggedIn()) {
            die("Not logged in");
        }

        $this->load->model('assignment');
        $this->assignment->loadById($assignmentId);
        $error = '';

        if ($this->input->post('Upload')) {
            if (!$_FILES['csvfile']['name']) {
                $error = "You must choose a file to upload";
            }
            else {
                $this->load->model($model);
                $error = $this->$model->upload($assignmentId,
                              $_FILES['csvfile']['tmp_name'], $params);
            }
        }
        if (!$this->input->post('Upload') || $error) {
            $this->_header('Upload spreadsheet');
            $this->load->view($view, array('assignment' => $this->assignment,
                                           'error' => $error));
        }
        else {
            $this->_header('Upload complete');
            $this->load->view('message', array(
             'message' => "Your spreadsheet has been successfully uploaded"));
        }
    }



    /** Unit test method just for testing upload of a spreadsheet.
     *  Used only by unit tester.
     */
    public function testSpreadsheetUpload($asstName, $spreadsheetModel, $filename, $params)
    {
        if (!$this->_isLoggedIn()) {
            die("Not logged in.");
        }
        $assignments = $this->assignment->getAllCurrentAssignments(FALSE, FALSE);
        $assignmentId = array_search($asstName, $assignments);
        if ($assignmentId === FALSE) {
            die("Couldn't match specified test assignment");
        }
        assert($assignmentId != 0);
        $error = $this->$spreadsheetModel->upload($assignmentId, $filename, $params);
        $this->_header('Upload complete');
        $message = $error ? "*** BLEW UP *** " . $error : "SUCCESSFUL";
        $this->load->view('message', array(
            'message' => $message));
    }
}

