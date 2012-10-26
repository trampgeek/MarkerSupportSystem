<?php
$simpleTestDir = dirname(__FILE__) . '/../simpletest/';

require_once($simpleTestDir . 'web_tester.php');
require_once($simpleTestDir . 'autorun.php');

ini_set('display_errors', 1);

define('MARKER_HOME', 'http://www.cosc.canterbury.ac.nz/richard.lobb/marking/marker');
define('ASST_NAME', 'JUNK999: MARKER_TESTING_DELETE_ME');

class TestMarkerController extends WebTestCase {
    var $instanceNum = 0;

    function __construct() {
        global $instanceNum;
        $instanceNum++;
        $this->instanceNum = $instanceNum;
    }
    function testLoginScreenAppears() {
        $this->assertTrue($this->get(MARKER_HOME));
        $this->assertText('Marker Login');
    }

    function testFailedLogin() {
        $this->assertTrue($this->get(MARKER_HOME));
        $this->assertText('Marker Login');
        $this->assertText('Assignment:');
        $this->assertTrue($this->setFieldByName('username', 'fred'));
        $this->assertTrue($this->setFieldByName('password', 'junk'));
        $this->assertTrue($this->setFieldByName('asstomark', ASST_NAME));
        $this->assertTrue($this->clickSubmit('Login'));
        $this->assertText('Authentication failed');
    }

    function testValidLogin() {
        $this->login();
        $this->assertText('Marking System (JUNK999 Marker)');;
        $this->assertText('rjl83:');
        $this->assertText('Choose Student');
        $this->assertText('Angus Clod');
        $this->assertText('Xin Zi');
        $this->assertText('Mark');
        $this->assertText('View marklog');
        $this->assertTrue($this->clickLink('Logout'));
        $this->assertText('Marker Login');
    }

    function testChoose() {
        $this->login();
        $this->assertTrue($this->clickLink('Mark'));
        $this->assertText('Marking abc001: Angus Clod');
        $this->assertText('Extra info: Stuff about Angus');
        $this->assertText('General');
        $this->assertText("Product Browser");
        $this->assertPattern("|Plausible use of session|");
        $this->assertFieldById('bonus', '0');
        $this->assertTrue($this->clickLink('Logout'));
    }

    function testMarking() {
        $this->login();
        $this->assertTrue($this->clickLink('Mark'));
        $this->assertText('Marking abc001: Angus Clod');
        $this->assertText('Extra info: Stuff about Angus');
        $browser = $this->getBrowser();
        $content = $browser->getContent();
        $matches = array();
        $patterns = array('Application launches', 'A useful and readable', 'Browser essentially works');
        $i = 0;
        foreach ($patterns as $pat) {
            $this->assertTrue(preg_match("|.*<textarea .+?name='(.+?)'.+?$pat.*|", $content, $matches));
            $inputName = $matches[1];
            $this->assertTrue(substr($inputName, 0, 4) == 'desc');
            $cbName = str_replace('desc', 'cb', $inputName);
            if ($i == 0) {
                $this->assertTrue($this->setFieldByName($inputName, 'blah comment blah'));  // change the first text
            }
            else if ($i == 1) {
                // Just the checkbox on this one
            }
            else {  // i == 2
                $this->assertTrue($this->setFieldByName($cbName.'wt', '0.25'));
            }
            $this->assertTrue($this->setFieldByName($cbName, 'on'));  // Select the checkbox for all of them
            $i += 1;
        }

        $this->assertTrue($this->setFieldByName('bonus', '2')); // Add a bonus of 2
        $this->assertTrue($this->setFieldByName('comments', 'My extra comment appears here')); // Add a comment
        $this->assertTrue($this->setFieldByName('markTotal', '17.5'));  // Set total as JavaScript can't
        $this->assertTrue($this->clickSubmit('Submit'));
        //echo $this->getBrowser()->getContent();
        $this->assertTrue($this->clickLink('Mark'));  // Reload the form after submission
        $this->assertPattern('|blah comment blah|');
        $this->assertPattern('|My extra comment appears here|');
        $this->assertPattern('|17.5|');  // If the mark made the round trip OK, we're good
    }


    // =================== PRIVATE SUPPORT FUNCTIONS ==================


    private function login() {
        $this->assertTrue($this->get(MARKER_HOME));
        $this->assertText('Marker Login');
        $this->assertTrue($this->setFieldByName('username', 'rjl83'));
        $this->assertTrue($this->setFieldByName('password', 'Murgatr0ad'));
        $this->assertTrue($this->setFieldByName('asstomark', ASST_NAME));
        $this->assertTrue($this->clickSubmit('Login'));
    }
}

?>
