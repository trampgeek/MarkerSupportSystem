<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class='choose'>
<h1>Upload CSV Spreadsheet of Mark Items</h1>
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
This function is only legal on a newly created assignment on which no marking
has yet been done.
</p>

<?php
if ($error) {
    echo "<p class='error'>$error</p>";
}

echo form_open_multipart('admin/uploadMarkitems/' . $assignment->id);
?>
<p>Select a .csv spreadsheet with three columns: markItemCategory, markItemDescription
and mark. The first row must contain a non-empty category. For all other rows,
if a category is omitted the category of the previous row(s) is assumed.</p>
<p>Select file: <input type='file' name='csvfile' /></p>
<?php echo form_submit('Upload', 'Upload'); ?>
</p>
<?php echo form_close(); ?>
</div>
<?php include('footer.php'); ?>
