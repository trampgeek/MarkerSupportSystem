<?php
// The standard header for all views. Takes $title as parameter.
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// LET THE OUTPUT BEGIN ....
echo doctype('html');
?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?php echo meta('Content-type', 'text/html; charset=utf-8', 'equiv'); ?>
        <title><?php echo $title; ?></title>

        <script src="<?php echo base_url(); ?>scripts/js/jquery-1.7.2.min.js"></script>
        <!--script src="<?php echo base_url(); ?>scripts/js/jquery-ui-1.8.23.custom.min.js"></script>
        <script src="<?php echo base_url(); ?>scripts/combobox.js"></script>
        <link rel="stylesheet" href="<?php echo base_url(); ?>scripts/css/smoothness/jquery-ui-1.8.23.custom.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>scripts/css/combobox.css" /-->

        <?php
        if ($scripts) {
            foreach ($scripts as $script) { ?>

        <script src="<?php echo base_url() . "scripts/$script"; ?>"></script>
        <?php

            }
        }

        $now = date('zGi');
        echo link_tag('css/stylemarkerstyles.css?version=' . $now); ?>

    </head>
    <body>

        <div class="banner">
            <h1>
                Marking System (<?php echo $course; ?> Marker)
            </h1>
        </div>

        <hr class='navbar' />
        <div class="navbar">
            <?php
            if ($username) {
                echo $username . ': &nbsp;&nbsp;';
                echo anchor('marker', 'Home');
                echo ' | ' . anchor('marker/printLogs', 'Print logs');
                echo ' | ' . anchor('marker/exportMarks', 'Export marks');
                echo ' | ' . anchor('marker/logout', 'Logout');
            }
            ?>

        </div>
        <hr class='navbar' />

        <?php
        if (isset($divclass)) {
            echo "<div class='$divclass'>\n";
        }
        ?>
