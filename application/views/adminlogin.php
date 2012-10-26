<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class='login'>
<h1><?php echo ucfirst('admin'); ?> Login</h1>
<?php if ($errMess) {
    echo "<p class='error'>$errMess</p>\n";
}
?>
<?php

echo form_open("admin", array('id'=>'loginform'));
?>

<table>
<tr><td>Username:</td><td><input type="text" name="username" value = "" /></td></tr>
<tr><td>Password: </td><td><input type="password" name="password" value = "" /></td></tr>
</table>
<p>
<?php echo form_submit('Login', 'Login'); ?>
</p>
<?php echo form_close(); ?>
</div>
<?php include('footer.php'); ?>
