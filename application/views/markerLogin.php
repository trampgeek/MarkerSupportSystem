<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class='login'>
<h1>Marker Login</h1>
<?php if ($errMess) {
    echo "<p class='error'>$errMess</p>\n";
}
?>
<p>Login with your usual U of C username and password, selecting the
assignment you wish to mark.
<?php

echo form_open("marker", array('id'=>'loginform'));
?>

<table>
<tr>
    <td>Username:</td>
    <td><input type="text" name="username" value = "" /></td>
</tr>
<tr>
    <td>Password: </td>
    <td><input type="password" name="password" value = "" /></td>
</tr>
<tr>
    <td>Assignment: </td>
    <td>
        <select name='asstomark'>
            <?php
            foreach($assList as $id=>$assName) {
                echo "<option value='$id'>$assName</option>\n";
            }
            ?>
        </select>
    </td>
</tr>
</table>
<p>
<?php echo form_submit('Login', 'Login'); ?>
</p>
<?php echo form_close(); ?>
</div>
<?php include('footer.php'); ?>
