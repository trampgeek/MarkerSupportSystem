<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class='choose'>
<h1>Upload CSV Spreadsheet of Students</h1>
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
This function is used to provide a list of students who can be marked for
the currently selected assignment. Each student in the list is added to
the database if they're not already there, and a dummy marksheet if created
for them unless one exists already. In the latter case, only the extrainfo
field of the marksheet is updated.
</p>

<?php
if ($error) {
    echo "<p class='error'>$error</p>";
}

echo form_open_multipart('admin/uploadStudents/' . $assignment->id);

?>
<p>Select a .csv spreadsheet with three columns: username, fullname
and "extrainfo" (additional information for markers).</p>
<p>Select file: <input type='file' name='csvfile' /></p>
<?php echo form_submit('Upload', 'Upload'); ?>
</p>
<?php echo form_close(); ?>
</div>
<?php include('footer.php'); ?>
