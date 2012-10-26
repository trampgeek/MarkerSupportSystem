<?php
// The header for all administrator views. Takes $title as parameter.
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// LET THE OUTPUT BEGIN ....
echo doctype('xhtml1-strict');
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $title; ?></title>
<?php echo meta('Content-type', 'text/html; charset=utf-8', 'equiv'); ?>
<?php $now=date('zGi'); ?>

<?php echo link_tag('css/stylemarkerstyles.css?version='.$now); ?>

</head>
<body>

<div class="banner">
<h1>
<?php echo $title; ?>
</h1>
</div>

<hr class='navbar' />
<div class="navbar">
<?php
    if ($username) {
        echo $username . ': &nbsp;&nbsp;';
        echo anchor('admin', 'Admin home');
        echo ' | ' . anchor('admin/editAssignment/0', 'New assignment');
        echo ' | ' . anchor('admin/about', 'About this site');
        echo ' | ' . anchor('admin/logout', 'Logout');
    }

 ?>

</div>
<hr class='navbar' />

<?php
if (isset($divclass)) {
    echo "<div class='$divclass'>\n";
}
?>
