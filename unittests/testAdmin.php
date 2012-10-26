<?php
$simpleTestDir = dirname(__FILE__) . '/../simpletest/';

require_once($simpleTestDir . 'web_tester.php');
require_once($simpleTestDir . 'autorun.php');

ini_set('display_errors', 1);

define('ADMIN_HOME', 'http://cosc.canterbury.ac.nz/richard.lobb/marking/admin');

class TestAdminController extends WebTestCase {
    var $instanceNum = 0;

    function __construct() {
        global $instanceNum;
        $instanceNum++;
        $this->instanceNum = $instanceNum;
        $this->defaultData = array('courseCode'  => 'JUNK999',
            'introduction'          => 'This is the introduction',
            'startingMark'          => '11.5',
            'pseudoMaxPenalty'      => '23.9',
            'outOf'                 => '33',
            'markers'               => 'abc,def, ghi ,k ,rjl83');
    }
    function testLoginScreenAppears() {
        $this->assertTrue($this->get(ADMIN_HOME));
        $this->assertText('Admin Login');
    }

    function testFailedLogin() {
        $this->assertTrue($this->get(ADMIN_HOME));
        $this->assertText('Admin Login');
        $this->assertTrue($this->setFieldByName('username', 'fred'));
        $this->assertTrue($this->setFieldByName('password', 'junk'));
        $this->assertTrue($this->clickSubmit('Login'));
        $this->assertText('Authentication failed');
    }

    function testValidLogin() {
        $this->login();
        $this->assertText('Assignment Marking System (Admin)');
        $this->assertText('rjl83:');
        $this->assertText('New assignment');
        $this->assertText('Admin home');
        $this->assertText('About this site');
        $this->assertText('Assignment Name'); // Arbitrary table header
        $this->assertTrue($this->clickLink('About this site'));
        $this->assertText('Goals');
        $this->assertText('The marking model');
        $this->assertTrue($this->clickLink('Admin home'));
        $this->assertText('Course Code');
        $this->assertText('Assignment Name');
    }


    function testCreateNewAndDelete() {
        $asstName = 'Testing create new. DELETE ME';
        $this->makeNewAssignment($asstName);
        $this->checkForm($asstName, $this->defaultData, 'abc, def, ghi, k, rjl83');
        $this->deleteAsst($asstName);
        $this->assertText('rjl83:');
        $this->assertText('New assignment');
        $this->assertText('Assignment Name'); // Check any arbitrary table header
    }


    function testEdit() {
        $origName = 'This should change';
        $this->makeNewAssignment($origName);
        $asstName = 'Edited Testing DELETEME';
        $newData = array(
            'courseCode'        => 'JUNK122',
            'assignmentName'    => $asstName,
            'startingMark'      => '11.6',
            'pseudoMaxPenalty'  => '23.8',
            'markers'           => 'b,   rjl83  ,  xx,y ');
        $this->fillInForm($asstName, $newData);
        $this->assertTrue($this->clickSubmit('Submit'));
        $this->openForEdit($asstName);
        $this->checkForm($asstName, $newData, 'b, rjl83, xx, y');
        $this->deleteAsst($asstName);
    }


    function testStudentUpload() {
        $asstName = 'TestingStudentUploadDELETEME';
        $this->makeNewAssignment($asstName);
        $this->assertTrue($this->clickLink('Upload students'));
        $this->assertText('Upload CSV Spreadsheet of Students');
        $this->assertTrue($this->clickSubmit('Upload'));
        $this->assertText('You must choose a file to upload');
        // TODO -- find a way to pass a file into the test. URL-encoding
        // it as a parameter doesn't work. So I've just hard coded it into
        // the testUploadStudents method -- yuck.
        $path = ADMIN_HOME . "/testUploadStudents/$asstName";
        $this->assertTrue($this->get($path));
        $this->assertText('SUCCESSFUL');
        $this->deleteAsst($asstName);
    }


    function testMarkitemUpload() {
         $asstName = 'TestingMarkUploadDELETEME';
         $this->makeNewAssignment($asstName);
         $this->assertTrue($this->clickLink('Upload mark items'));
         $this->assertText("Upload CSV Spreadsheet of Mark Items");
         $this->assertTrue($this->clickSubmit('Upload'));
         $this->assertText('You must choose a file to upload');
         // TODO -- find a way to pass a file into the test. URL-encoding
         // it as a parameter doesn't work. So I've just hard coded it into
         // the testUploadStudents method -- yuck.
         $path = ADMIN_HOME . "/testUploadMarkitems/$asstName";
         $this->assertTrue($this->get($path));
         $this->assertText('SUCCESSFUL');
         $this->deleteAsst($asstName);
    }

    // This last test case leaves a single assignment behind for marker testing
    function testFinal() {
        $asstName = "MARKER_TESTING_DELETE_ME";
        $this->login();
        $this->assertTrue($this->clickLink('Admin home'));
        $content = $this->getBrowser()->getContent();
        if (strpos($content, $asstName) === FALSE) {
            $this->makeNewAssignment($asstName, $this->defaultData, FALSE);
            $path = ADMIN_HOME . "/testUploadStudents/$asstName";
            $this->assertTrue($this->get($path));
            $this->assertText('SUCCESSFUL');
            $path = ADMIN_HOME . "/testUploadMarkitems/$asstName";
            $this->assertTrue($this->get($path));
            $this->assertText('SUCCESSFUL');
        }
    }

    // ================= SUPPORT FUNCTIONS =====================

    // Makes an assignment of the given name, saves it and re-opens it for editing.
    private function makeNewAssignment($asstName, $data = NULL, $loginReqd = TRUE) {
        if (!$data) {
            $data = $this->defaultData;
        }
        if ($loginReqd) {
            $this->login();
        }
        $this->assertTrue($this->clickLink('Create new assignment'));
        $this->assertText("New assignment");
        $this->fillInForm($asstName, $data);
        $this->assertTrue($this->clickSubmit('Submit'));
        $this->assertNoText('Pseudo');  // Should be back to the admin home page
        $this->assertText($asstName);
        $this->openForEdit($asstName);
        $this->assertPattern("|$asstName|");
    }



    private function openForEdit($asstName) {
        $id = $this->getAsstId($asstName);
        $this->assertTrue($this->clickLinkById('edit'.$id));
    }


    private function deleteAsst($asstName) {
        $id = $this->getAsstId($asstName);
        $this->assertTrue($this->clickLinkById('delete'. $id));
        $this->click('Yes, do it!');
        $this->assertNoPattern("|$asstName|");
    }


    // Selects the admin home page, gets the assignment ID from it
    private function getAsstId($asstName) {
        $this->assertTrue($this->clickLink('Admin home'));
        $page = $this->getBrowser()->getContent();
        $matches = array();
        $this->assertTrue(preg_match("|<td>$asstName</td>.+?/admin/delete/([0-9]+)|ms", $page, $matches));
        return $matches[1];
    }

    private function login() {
        $this->assertTrue($this->get(ADMIN_HOME));
        $this->assertText('Admin Login');
        $this->assertTrue($this->setFieldByName('username', 'rjl83'));
        $this->assertTrue($this->setFieldByName('password', 'Murgatr0ad'));
        $this->assertTrue($this->clickSubmit('Login'));
    }


    private function fillInForm($asstName, $data) {
        $data['assignmentName'] = $asstName;
        foreach ($data as $k => $v) {
            $this->assertTrue($this->setFieldByName($k, $v));
        }
    }


    private function checkForm($asstName, $data, $markers) {
        $this->assertField('assignmentName', $asstName);
        foreach ($data as $field => $value) {
            if ($field != 'markers') {
                $this->assertField($field, $value);
            }
        }
        $this->assertField('markers', $markers);
    }

}

?>
