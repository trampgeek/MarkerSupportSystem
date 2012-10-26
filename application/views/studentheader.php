<?php
// The standard header for all student views. Takes $title as parameter.
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// LET THE OUTPUT BEGIN ....
if (isset($doctype)) {
	echo doctype($doctype);
}
else {
    echo doctype('xhtml1-strict');
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $title; ?></title>
<?php echo meta('Content-type', 'text/html; charset=utf-8', 'equiv'); ?>
<?php $now=date('zGi'); ?>

<?php echo link_tag('css/stylemarkerstyles.css?version='.$now);

if (isset($headElements)) {
	foreach ($headElements as $element) {
		echo $element . "\n";
	}
}

?>

</head>
<body>

<div class="banner">
<h1>
Assignment Marking System
</h1>
</div>

<hr class='navbar' />
<div class="navbar">
<?php
    if ($username) {
        echo $username . ': &nbsp;&nbsp;';
        //echo anchor('student/choosePartner', 'Choose partner');
        echo anchor('student/logout', 'Logout');
        // echo ' | ' . anchor('student/showCorrectnessLog', 'Correctness log');
        // echo ' | ' . anchor('student/display', 'Style log');
        // echo ' | ' . anchor('student/showGuiLog', 'GUI log');
        // echo ' | ' . anchor('student/displaySummary', 'Summary');
    }


 ?>

</div>
<hr class='navbar' />

<?php
if (isset($divclass)) {
    echo "<div class='$divclass'>\n";
}
?>
