<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>


<?php
echo form_open('student/login/');
?>


<h1>Login</h1>
<?php if ($errMess) {
    echo "<p class='error'>$errMess</p>\n";
}
?>
<p>
Please log in with your usual University (ITCS) username and password.
</p>
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
        <select name='assignmentId'>
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


<?php include('footer.php'); ?>
