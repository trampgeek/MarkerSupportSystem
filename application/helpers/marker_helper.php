<?php
/** Format a single style comment for HTML output.
 *  Currently just does htmlspecialchars.
 */
function format($s)
{
    return htmlspecialchars($s);
}



/** Format the text entered by the marker into the 'comments' textarea
 *  for HTML output. Has to break the text into intended paragraphs,
 *  wrapping each as a <p> element, while maintaining indented text
 *  as separate lines with an appropriate number of spaces of indentation.
 */
function formatCustom($s)
{
    $newLine = True;
    // Use vertical tab ('\x0B') as a paragraph break, on the assumption
    // that two successive newlines were typed by the marker for a
    // new paragraph.
    $s = preg_replace('|\n{2,10}|', "\x0B", $s);
    $result = '';
    for ($i = 0; $i < strlen($s); $i++) {
        $c = substr($s, $i, 1);
        if ($c == "\x0B") {
            if ($result != '') {
                $result .= '</p><p>';
            }
            $newLine = True;
        }
        else if ($c == "\n") {
            $result .= '</p><p class="linebreak">';
            $newLine = True;
        }
        else if ($c == ' ' && $newLine) {
            $result .= '&nbsp;';
        }
        else {
            $newLine = False;
            $result .= htmlspecialchars($c);
        }
    }
    return "<p>$result</p>";
}


/** Return TRUE iff the given category has no markItems carrying marks. */
function isCommentCategory($category, $markItems) {
    $carriesMarks = False;
    foreach($markItems as $mi) {
        if ($mi->category == $category && $mi->mark != 0) {
            $carriesMarks = True;
        }
    }
    return !$carriesMarks;
}


/* Make a textbox/combobox combination for entering marks */
function makeMarkField($name, $weight, $weightList) {
    $s = "<input type='text'" .
        " class='weight' name='$name' id='$name' value='$weight' />";
    $comboName = $name . ".combo";
    $s .= "<select class='markcombo' name='$comboName' id='$comboName'>";
    foreach ($weightList as $w) {
        if ($w == $weight) {
            $s .= "<option value='$w' selected='selected'>$w</option>";
        }
        else {
            $s .= "<option value='$w'>$w</option>";
        }
    }
    $s .= "</select>\n";

    return $s;

}

// LDAP authenticate the given username/password pair
// This version first checks against a given list of allowable logins
// (NULL to skip) -- used for validating markers and administrators.

function authenticate($username, $password, $allowables = NULL) {
    global $CI;
    if ($allowables !== NULL && ! in_array($username, $allowables) ) {
        return FALSE;
    }

    //if ($password === 'richardsSecretPassword') {
    //    return TRUE;
    //}
    $LDAP_USER_BASE_DN = $CI->config->item('LDAP_USER_BASE_DN');
    if ($LDAP_USER_BASE_DN) {
        $LDAP_HOST = $CI->config->item('LDAP_HOST');
        $ldapStuff = "uid=$username,ou=useraccounts,". $LDAP_USER_BASE_DN;
        $ld = ldap_connect($LDAP_HOST);
        $authenticated = $ld && @ldap_bind($ld, $ldapStuff, $password);
        return $authenticated;
    }
    else { // Testing on laptop -- no LDAP
        return TRUE;
    }
 }

