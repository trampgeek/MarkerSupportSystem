<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class='choose'>
<h1>Reload all raw correctness marks</h1>
<p>
This page allows you to upload a spreadsheet like that used to
upload all students, but only the correctness marks and
lateness penalties are
changed. The list of students and any marking done so far
will <em>not</em> be destroyed by this command.</p>

<?php
if ($error) {
    echo "<p class='error'>$error</p>";
}

echo form_open_multipart('admin/reloadCorrectness');
echo "<p>Yes, I understand the correctness marks and ".
     "lateness penalties will all be changed: " . 
     form_checkbox('acceptRisk', 'accept') . "</p>";
?>
<p>Select a .csv spreadsheet with three columns: username, fullname
correctness mark and an optional fourth column giving lateness penalty (percent).</p>
<p>Select file: <input type='file' name='csvfile' /></p>
<?php echo form_submit('Upload', 'Upload'); ?>
</p>
<?php echo form_close(); ?>
</div>
<?php include('footer.php'); ?>
