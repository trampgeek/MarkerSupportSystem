<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>

<div class='marking'>
<?php
echo "<input type='hidden' id='pseudoMaxPenalty' value='{$assignment->pseudoMaxPenalty}'>\n";
echo "<input type='hidden' id='startingMark' value='{$assignment->startingMark}'>\n";
echo form_open("marker/processMarking/",
    array('id'=> 'markingForm'),
    array('marksheetId' => $marksheet->id,
          'extraCommentCount' => '0'));
?>
<?php
echo "<h1>Marking {$student->username}: {$student->name}</h1>\n";
if ($marksheet->extraInfo) {
    echo "<p>Extra info: {$marksheet->extraInfo}</p>\n";
}
$assignmentId = $assignment->id;
$categoryList = $categories->getCategories($assignmentId);
$preferred = array("0","0.1","0.2","0.25", "0.3","0.3333","0.4","0.5","0.6","0.6667", "0.7","0.75", "0.8","0.9", "1.0");

foreach ($categoryList as $category) {
    $catId = $category->id;
    $categoryDesc = $category->category;
    $markItemList = $markitems->getMarkItems($assignmentId, $marksheet->id, $catId, $marksheet->markerId);
    $thisSheetsMarkItems = $markitems->getSelectedItems($marksheet->id);
    echo "<h2 class='category' >$categoryDesc</h2>\n";
    echo "<p><table class='markItems' id='markItems$catId'>\n";
    foreach ($markItemList as $markItem) {
        if ($markItem->mark > 0) {
            $type = 'reward';
        }
        else if ($markItem->mark == 0) {
            $type = 'comment';
        }
        else {
            $type = 'penalty';
        }
        $id = $markItem->id;
        $descName = "desc$id";
        $idName = "ta$id";
        $comment = $markItem->comment;
        if (array_key_exists($id, $thisSheetsMarkItems)) {
            $weight = $thisSheetsMarkItems[$id]->weight;
            $checked = " checked='checked'";
            if ($thisSheetsMarkItems[$id]->commentOverride) {
                $comment = $thisSheetsMarkItems[$id]->commentOverride;
            }
        }
        else {
            $weight = 1;
            $checked = '';
        }
        $value = form_prep($comment);
        echo "<tr class='$type'><td class='description'>" .
        "<textarea id='$idName' class='comment' height='1' width='80' name='$descName'" .
         " title='$value' >$value</textarea>" .
         "<input type='hidden' name='tc$id' id='tc$id' value=''/></td>";
        $cbid = "cb" . $markItem->id;
        echo "<td><input type='checkbox' class='item-checkbox' id='$cbid' name='$cbid' $checked";
        echo "/></td><td class='mark' id='mark{$markItem->id}'>{$markItem->mark}</td>";
        if ($markItem->mark != 0) {
            $name = $cbid . 'wt';
            echo "<td class='weight'>" . makeMarkField($name, $weight, $preferred) . "</td>";
        }

        echo "</tr>\n";
    }

    echo "</table></p>\n";

    echo "<input type='button' class='add-comment-button' id='add$catId' value='+' />";
    echo "<input type='button' class='remove-comment-button' id='remove$catId' value='-' />";
}

echo "<h2 class='comments'>Extra comments</h2><p><textarea class='comments' name='comments' rows='10' cols='120' >\n";
echo "{$marksheet->comments}</textarea></p>\n";
echo "<p><table class='summary'>";
echo "<tr><td>Bonus:</td><td><input type='text' name='bonus' id='bonus'
        value='{$marksheet->bonus}' /></td></tr>\n";
echo "<tr><td>TOTAL MARK:</td><td><input type='text' id='markTotal'" .
     " readonly='readonly'" .
     " value='{$marksheet->markTotal}' name='markTotal' /></tr</td>\n";
echo "</table>\n";

?>

<?php echo form_submit('submitmark', 'Submit'); ?>
</p>
<?php echo form_close(); ?>
</div>
<?php include('footer.php'); ?>

