<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<h1><?php echo $title; ?></h1>

<?php

/** Helper function for filling in form fields */
function mySetValue($field, $assignment) {
    return set_value($field, $assignment->$field);
}

$markersString = implode(", ", $markers);
$errors = validation_errors();
if ($errors) {
        echo "$errors\n";
}

$id = $assignment->id;
if ($id == 0) {
    $id = 0;  // Converts NULL to 0 too
    ?>
<p>When constructing a new assignment, set the main details in the form
    below first and submit. Then, from back in the administrator home page,
    click the <em>edit</em> link for this assignment to add mark items and
    students.
</p>
<?
}

echo form_open("admin/editAssignment/$id");
$a = $assignment;
?>

<table>
<tr>
    <td>Course code:</td>
    <td><input type="text" name="courseCode"
               value = "<?php echo mySetValue('courseCode', $a); ?>" />
    </td>
</tr>
<tr>
    <td>Assignment name:</td>
    <td><input type="text" name="assignmentName"
               value = "<?php echo mySetValue('assignmentName', $a); ?>" />
    </td>
    <tr>
    <td>Introduction:</td>
    <td><textarea rows='5' cols='60' class='introduction' name='introduction'><?php echo mySetValue('introduction', $a); ?></textarea>
    </td>
</tr>
<tr>
    <td>Starting mark:</td>
    <td><input type="text" name="startingMark"
               value = "<?php echo mySetValue('startingMark', $a); ?>" />
    </td>
</tr>
<tr>
    <td>Pseudomax penalty: </td>
    <td><input type="text" name="pseudoMaxPenalty"
               value = "<?php echo mySetValue('pseudoMaxPenalty', $a); ?>" />
    </td>
</tr>
<tr>
    <td>Mark is out of: </td>
    <td><input type="text" name="outOf"
               value = "<?php echo mySetValue('outOf', $a); ?>" />
    </td>
</tr>
<tr>
    <td>Display item marks: </td>

    <td><select name='markDisplayToStudent'>
    <?php
    $markDisplayValue = mySetValue('markDisplayToStudent', $a);
    foreach(array('FULL' => 'Full', 'PARTIAL' => 'Partial',
                  'NONE' => 'Comments only') as $value => $display) {
        if ($value == $markDisplayValue) {
            echo "<option value='$value' selected='selected'>$display</option>\n";
        }
        else {
            echo "<option value='$value'>$display</option>\n";
        }
    } ?>

        </select>
    </td>
</tr>
<tr>
    <td>Markers:</td>
    <td><input type="text" name="markers"
               value = "<?php echo $markersString; ?>" />
    </td>
</tr>
</table>
<p>
<?php echo form_submit('Submit', 'Submit');

if ($assignment->id) { ?>
<hr>
<h1>Assignment details</h1>
<ul>
   <li>
   <a href="<?php echo site_url("admin/uploadStudents/{$assignment->id}"); ?>">Upload students</a>
    </li>
   <li>
   <a href="<?php echo site_url("admin/uploadMarkitems/{$assignment->id}"); ?>">Upload mark items</a>
    </li>

</ul>
<?php
}
?>


</p>
<?php

echo form_close();
include('footer.php');

?>
