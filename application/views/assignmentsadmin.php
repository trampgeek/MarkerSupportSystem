<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<h1>Assignments</h1>
<table>
    <tr>
        <th></th>
        <th>Course Code</th>
        <th>Assignment Name</th>
        <th>Created</th>
        <th></th>
    </tr>
    <?php foreach($assignments as $ass) {
    ?>
     <tr>
        <td><a href="<?php echo site_url("admin/editAssignment/" . $ass->id); ?>" id ="edit<?php echo $ass->id; ?>">Edit</a></td>
        <td><?php echo $ass->courseCode; ?></td>
        <td><?php echo $ass->assignmentName; ?></td>
        <td><?php echo $ass->timestamp; ?></td>
        <td><a href="<?php echo site_url("admin/delete/" . $ass->id); ?>" id ="delete<?php echo $ass->id; ?>">Delete</a></td>
    </tr>
    <?} ?>
</table>
<p>
    <a href="<?php echo site_url("admin/editAssignment/0"); ?>">Create new assignment</a>
</p>
