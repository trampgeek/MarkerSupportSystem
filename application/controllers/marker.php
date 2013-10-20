<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/** The controller for the marker functionality.
 *  This implementation is intended to handle both a positive (reward-
 *  based) marking system and a negative (penalty-based) marking system,
 *  or some mix of the two.
 *
 *  In a positive marking system the total mark is broken down into a
 *  set of checkbox items. The marker ticks those items that are
 *  satisfied by the student's submission, with the possibility of
 *  adjusting the weight value (a number from 0 to 1) by means of an
 *  extra text box. The student's mark is the sum of the products of the
 *  weight factors on each checked item and the marks awarded for that
 *  item.
 *
 *  In a penalty based marking system, the total mark starts off at
 *  some fixed value (representing 100%) and is decremented by a preset
 *  amount for each checked penalty item. With this system there might
 *  be a very large set of possible penalty items so there is an
 *  additional "pseudoMaxPenalty" that is essentially a weighting factor
 *  applied to the sum of all the penalties.
 *
 *  If a markItem carries a negative mark it is deemed to be a penalty,
 *  a markItem with zero mark is called a comment and a markItem with positive
 *  weight is a reward. You must decide whether you're running a reward system,
 *  in which case you use rewards and comments, or a penalty system, in which
 *  case you use penalties and comments. Do not mix rewards and penalties
 *  within the same system or you'll find things get very confusing!
 *  The total computed mark is given by
 *
 *    mark = startingMark + sum(reward[i] * weight[i]) -
 *           sum(penalty[i] * weight[i]) / pseudoMaxPenalty
 *
 *  A pure "style factor" version is obtained by setting the starting mark
 *  to 1, using only penalties with typical mark values of -1 and
 *  setting psuedoMaxPenalty to a suitably large value so that most
 *  students finish up with a mark of around 0.5. Comments may also be used,
 *  e.g. for positive-sounding remarks.
 */


define('DEBUG', 1);
define('MAX_ERROR', 0.000001);
// CHANGE IN student.php TOO!  TODO: FixMe
// TODO: decide what on earth the relevance of the following line is/was
// define('RESULTS_BASE', '/home/cosc/tutor/c121mark/121s1-12/Automarker-main/marking/results');

if (defined('DEBUG') && DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors','1');
}


class Marker extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('student');
        $this->load->model('assignment');
        $this->load->model('marksheet');
        $this->load->model('markitems');
        $this->load->model('categories');
        $this->load->model('markers');
        $this->load->helper(array('form', 'url', 'html', 'marker'));
        if ($this->_isLoggedIn()) {
            $this->assignment->loadById($this->session->userdata('assignmentId'));
        }
    }


    public function index()
    {
        if (!$this->_isLoggedIn()) {
            $this->doLogin();
        }
        else {
            $this->choose();
        }
    }



    public function doLogin()
    {
        $assList = $this->assignment->getAllCurrentAssignments();
        $this->_login($assList);
    }


    public function logout()
    {
        $this->session->unset_userdata('marker');
        $this->doLogin();
    }



    public function choose($prevStudId='')
    {
        if (!$this->_isLoggedIn()) {
            die("Intruder alert");
        }

        $this->_header($this->assignment->courseCode . ' Assignment Marking',
                array('jquery-1.8.2.min.js'));
        $this->load->view('choose', array(
            'marksheets'    =>  $this->marksheet->getAllMarksheets($this->assignment->id),
            'markers'       =>  $this->markers,
            'selectedStud'  =>  $prevStudId)
        );
    }


    public function mark($studentId)
    {
        if (!$this->_isLoggedIn()) {
            $this->doLogin();
            return;
        }
        $this->_header($this->assignment->courseCode . ' marksheet', array(
            'jquery-1.8.2.min.js', 'autosize.js', 'doMarking.js'));
        if (!$this->student->loadById($studentId)) {
            die("Oops. Non-existent student?!");
        }
        if (!$this->marksheet->load($this->assignment->id, $studentId)) {
            die("Oops. Non-existent marksheet?!");
        }
        $this->marksheet->markerId = $this->session->userdata('markerId');
        $this->load->view('doMarking',
            array(
                'student'    => $this->student,
                'assignment' => $this->assignment,
                'marksheet'  => $this->marksheet,
                'markitems'  => $this->markitems,
                'categories' => $this->categories)
        );
    }

    // Process a submitted marking form.
    public function processMarking()
    {
        if (!$this->_isLoggedIn()) {
            $this->doLogin();
        }
        elseif ($this->input->post('marksheetId') === FALSE) {
            $this->_header($this->assignment->courseCode . ' student marksheet');
            $this->load->view('message', array('message'=>'No marksheetId?! SOMETHING HAS GONE SERIOUSLY WRONG!'));
        }
        else {
            $fields = $this->input->post(NULL); // Get all input fields
            $marksheetId = $fields['marksheetId'];
            $this->marksheet->loadById($marksheetId);
            $this->assignment->loadById($this->marksheet->assignmentId);
            $this->markitems->clearMarksheetMarkitems($marksheetId);
            list($rewards, $penalties) = $this->processAllMarkItems($fields);

            $mark = $this->assignment->startingMark + $rewards +
                    $penalties / $this->assignment->pseudoMaxPenalty +
                    $fields['bonus'];

            $this->marksheet->markerId = $this->session->userdata('markerId');
            $this->marksheet->bonus = $fields['bonus'];
            $this->marksheet->comments = $fields['comments'];
            $this->marksheet->markTotal = $mark;
            $this->marksheet->update();

            $jsMark = $fields['markTotal'];
            if ($mark != $jsMark) {
                $error = "*** WARNING: computed mark ($mark) differs from JavaScript computed mark ($jsMark)";
                $this->_header($this->assignment->courseCode . ' student marksheet');
                $this->load->view('message', array('message' => $error));
            }
            else {
                $this->choose($this->marksheet->studentId);
            }
        }
    }


    public function help()
    {
        $this->_header("Help");
        $this->load->view('help');
    }


    public function about()
    {
        $this->_header("About");
        $this->load->view('about');
    }


    // Process all the mark items to be found in the given list of form fields.
    private function processAllMarkitems($fields) {
        $penalties = 0;
        $rewards = 0;
        $matches = array();
        foreach ($fields as $fieldName=>$value) {
            if (preg_match('|^cb([0-9]+)$|', $fieldName, $matches)) {
                // Selection of a 'base' mark item
                list($rewards, $penalties) = $this->processBaseItem(
                    $matches[1], $fields, $rewards, $penalties);
            }
            elseif (preg_match('|^xcb([0-9]+)$|', $fieldName, $matches)) {
                // A newly added markitem (must be a comment)
                $markItemId = $matches[1];
                $persists = isset($fields['persist' . $markItemId]);
                $this->processNewMarkitem($markItemId, $fields, TRUE, $persists);
            }
            elseif (preg_match('|^discontinue([0-9]+)$|', $fieldName, $matches)) {
                // An old mark item with the 'discontinue' checkbox set.
                $markItemId = $matches[1];
                $this->markitems->discontinue($markItemId);
            }
        }
        return array($rewards, $penalties);
    }

    // Process one of the "base" fields, with given id, given all the other
    // fields and the current rewards/penalties. Returns a new pair
    // of rewards/penalties.
    private function processBaseItem($markItemId, $fields, $rewards, $penalties) {
        $comment = $fields['desc'.$markItemId];
        $this->markitems->loadById($markItemId);
        $markItem = $this->markitems;
        $weight = $markItem->mark == 0 ? 0 : $fields['cb'.$markItemId.'wt'];
        $commentOverride = $comment != $markItem->comment ? $comment : NULL;

        $this->markitems->insertMarksheetItem(
                $fields['marksheetId'],
                $markItemId,
                $weight,
                $commentOverride);

        if ($markItem->mark > 0) {
            $rewards += $markItem->mark * $weight;
        }
        else if ($markItem->mark < 0) {
            $penalties += $markItem->mark * $weight;
        }

        return array($rewards, $penalties);
    }


    // Process a newly added markitem with the given ID and the other given fields.
    // $usedHere is TRUE iff mark item applies to this sheet, $persists is TRUE
    // iff it should be persistent. [An item may be set to persist but not
    // actually be used on this sheet.]
    private function processNewMarkitem($markItemId, $fields, $usedHere, $persists) {
        $comment = $fields['extraComment'.$markItemId];
        $catId = $fields['xccatid'.$markItemId];
        $newItemId = $this->markitems->insertItem(
                $this->assignment->id,
                $catId,
                $comment,
                0,
                $this->session->userdata('markerId'),
                $persists);

        if ($usedHere) {
            $this->markitems->insertMarksheetItem(
                $fields['marksheetId'],
                $newItemId,
                0);
        }
    }

    // Compute and return the mark for the given marksheet.
    private function computeMark($marksheetId)
    {
        $this->marksheet->loadById($marksheetId);
        if ($this->marksheet->markerId == 0) {
            $mark = 0;
        }
        else {
            $selectedItems = $this->markitems->getSelectedItems($marksheetId);
            $this->assignment->loadById($this->marksheet->assignmentId);
            $mark = $this->assignment->startingMark + $this->marksheet->bonus;
            $penalties = 0;
            foreach ($selectedItems as $item) {
                $contrib = $item->mark * $item->weight;
                if ($contrib < 0) {
                    $penalties += $contrib;
                } else {
                    $mark += $contrib;
                }
            }
            $mark += ($penalties / $this->assignment->pseudoMaxPenalty);
        }
        return $mark;
    }


    // Test function to make sure that the computed marks for all current
    // active marksheets agrees with the freshly recomputed value.
    public function testComputeMark()
    {
        if (!$this->_isLoggedIn()) {
            $this->doLogin();
            return;
        }
        $errCnt = 0;
        foreach($this->assignment->getAll() as $asst) {
            foreach($this->marksheet->getAllMarksheets($asst->id) as $sheet) {
                $computed = $this->computeMark($sheet->id);
                if ($sheet->markTotal != $computed) {
                    $errCnt++;
                    echo ("Error for marksheet {$sheet->id}, asst {$asst->id}. ");
                    echo ("Stored value {$sheet->markTotal}, computed $computed");
                }
            }
        }
        if ($errCnt == 0) {
            echo "All good";
        }
    }


   /**
     * Display an individual student's marklog.
     * This is the marks recorded *for this student*, as distinct
     * from the mark of the partnership.
     */
     public function showMarklog($studentId)
     {
        if (!$this->_isLoggedIn()) {
            $this->doLogin();
            return;
        }

        $this->_header($this->assignment->courseCode . ' student marksheet');
        $this->marksheet->load($this->assignment->id, $studentId);
        if ($this->marksheet->id == 0) {
            $this->load->view('message',
                array('message'=>'No marksheet found for this student'));
        }
        else {
            $this->load->view('marklog', array(
                'markitems'     => $this->markitems,
                'marksheet'     => $this->marksheet,
                'assignment'    => $this->assignment,
                'categories'    => $this->categories,
                'student'     => $this->student)
            );
            $this->load->view('footer');
        }
     }


    /** Generate the marklogs.
     *  Response is a sequence of marklogs. This
     *  will need to be split into individual files by a postprocess.
     */
    public function printLogs()
    {
        if (!$this->_isLoggedIn()) {
            $this->doLogin();
            return;
        }

        $this->_header('All marklogs');
        $first = TRUE;
        $sheets = $this->marksheet->getAllMarksheets($this->assignment->id);
        foreach ($sheets as $sheet) {
            if (!$first) {
                $this->load->view('horizontalRule');  // Separator
            }
            $this->marksheet->load($this->assignment->id, $sheet->studentId);
            $this->load->view('marklog', array(
                'markitems'     => $this->markitems,
                'marksheet'     => $this->marksheet,
                'assignment'    => $this->assignment,
                'categories'    => $this->categories,
                'student'       => $this->student)
            );

            $first = FALSE;
        }
        $this->load->view('footer');
    }


    /**
     * Output a CSV file of the marks
     */
    public function exportMarks()
    {
        if (!$this->_isLoggedIn()) {
            $this->doLogin();
            return;
        }
        $this->_header('Export marks');
        $marks = array();
        $sheets = $this->marksheet->getAllMarksheets($this->assignment->id);
        foreach ($sheets as $sheet) {
            $this->student->loadById($sheet->studentId);
            $mark = $this->computeMark($sheet->id);
            $row = array('username' => $this->student->username,
                         'marker'   => $sheet->marker,
                         'name'     => $this->student->name,
                         'mark'     => $mark);
            if (abs($mark - $sheet->markTotal) > MAX_ERROR) {
                $row['error'] = sprintf("***COMPUTED AND STORED MARKS DIFFER*** " .
                    "(%.5f, %.5f)", $mark, $sheet->markTotal);
            }
            $marks[] = $row;
        }
        $this->load->view('markexport', array('marks' => $marks));
    }


    // PRIVATE/PROTECTED SUPPORT FUNCTIONS
    // ===================================

    protected function _isLoggedIn() {
        return $this->session->userdata('marker') !== FALSE;
    }

    protected function _recordLogin($username, $assignmentId) {
        $this->session->set_userdata('marker',  $username);
        $this->session->set_userdata('markerId',
                $this->markers->getMarkerId($username, $assignmentId));
        $this->session->set_userdata('assignmentId', $assignmentId);
    }

    // The name of the currently logged in user, not to be confused with
    // the username of the student being marked :(
    protected function _getUsername() {
        return $this->session->userdata('marker');
    }

   /**  Login function.
     *  Firstly, uses the Uni LDAP server for authentication.
     *  Then checks if the marker is authorised to mark their chosen
     *  assignment.
     */
    protected function _login($assList)
    {
        $this->load->helper('marker');
        $errMess = '';

        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $assignmentId = $this->input->post('asstomark');
        if ( $username && $password ) {
            if (authenticate($username, $password)) {
                $this->assignment->loadById($assignmentId);
                $markers = $this->markers->getMarkers($assignmentId);
                if (in_array($username, $markers)) {
                    $this->_recordLogin($username, $assignmentId);
                }
                else {
                    $errMess = "Sorry, you're not authorised to mark this assignment";
                }
            }
            else {
                $errMess = 'Authentication failed';
            }
        }

        if ($this->_isLoggedIn()) {
            $this->choose();
        }
        else {
            $this->_header('Assignment Marking system');
            $this->load->view('markerLogin', array(
                            'assList'   => $assList,
                            'errMess'   => $errMess)
            );
        }


    }


    // Generate the header
    protected function _header($title, $scripts = NULL) {
        $this->load->view('markerHeader', array(
            'title'  => $title,
            'course' => $this->assignment->courseCode,
            'username' => $this->session->userdata('marker'),
            'scripts' => $scripts
       ));
    }

}

