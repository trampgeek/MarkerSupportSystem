<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class='choose'>
<h1>Upload CSV Spreadsheet of External Marked Grades</h1>
<p>*** NOT FOR GENERAL USE. ***</p>
<table>
    <tr>
        <td>Course:</td>
        <td><?php echo $assignment->courseCode; ?></td>
    </tr>
    <tr>
        <td>Assignment:</td><td><?php echo $assignment->assignmentName; ?></td>
    </tr>
</table>
<p>
This function will discard any existing marking on an assignment and replace
it with results uploaded from a specially constructed (read "hacked together")
spreadsheet. Proceed at your peril!
</p>

<?php
if ($error) {
    echo "<p class='error'>$error</p>";
}

echo form_open_multipart('admin/uploadExternalGrades/' . $assignment->id);
?>
<p>Select a .csv spreadsheet with the first row containing mark item ids
(yes, the internal values for the mark item table!) in columns 3 onwards
of row 1. Row 2 is ignored. Rows 3 on contain usernames in column 1 and
actual marks for the mark items in the header row in columns 3 onwards,
with a total in the final (extra) column.</p>
<p>Select file: <input type='file' name='csvfile' /></p>
<?php echo form_submit('Upload', 'Upload'); ?>
</p>
<?php echo form_close(); ?>
</div>
<?php include('footer.php'); ?>
