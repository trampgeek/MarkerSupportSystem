<h1>Confirm Delete.</h1>
WARNING! You are about to delete assignment
<?php
echo $assignment->courseCode . ': ' . $assignment->assignmentName . "<br .>";
echo form_open('admin/delete/' . $assignment->id);
?>
<input type="submit" name="confirmDelete" value="Yes, do it!" />
<input type="submit" name="cancelDelete" value="No no no!" />
<?php echo form_close();
include "footer.php";
?>

