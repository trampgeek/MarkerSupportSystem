<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<script type="text/javascript">
function doSubmit() {
    var f = document.forms[0];
    f.submit();
}
</script>
<div class='choose'>
<h1>Choose Partner</h1>  
<p>Use the combo box to select your partner. Selections must be finalised by
Sunday May 13. For a selection to be valid, <b></b>you and your partner must both
have selected each other by May 13.
 </p>
<?php
echo form_open("student/choosePartner", array('id'=>'choosepartner'));
echo "<p><select name='partnerUsername' id='partnerUsername' size='20' ondblclick='doSubmit()'>\n";
$selected = $partnerUsername == '' ? ' selected' : '';
echo "<option value=''$selected>No partner (solo)</option>\n";

foreach ($students as $student) {
    $name = $student->name;
    $candidateUsername = $student->username;
    $selected = $partnerUsername == $candidateUsername ? " selected" : '';
    echo "<option value='$candidateUsername'$selected>$name</option>\n";
}

?>
</select>
</p>
<p>
<?php echo form_submit('Submit', 'Submit'); ?>
</p>
<?php echo form_close(); ?>
</div>
<?php include('footer.php'); ?>
