<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . '/views/Markdown/markdown.php');

$assignment->loadById($marksheet->assignmentId);
$student->loadById($marksheet->studentId);
$assignmentId = $assignment->id;
$displayType = $assignment->markDisplayToStudent;
?>

<div class='marklog'>
<h1>Marklog for <?php echo "{$student->name} ({$student->username})" ?></h1>

<?php


if ($marksheet->markerId == 0) {
    echo "<p class='comment'>This assignment hasn't been marked yet.</p>";
}
else {
    $categoryList = $categories->getCategories($assignmentId);

    foreach ($categoryList as $category) {

        $categoryDesc = $category->category;
        $thisSheetsMarkItems = $markitems->getSelectedItemsInCategory($marksheet->id, $category->id);
        if (count($thisSheetsMarkItems) == 0) {
            continue;
        }
        $firstMarkItem = $thisSheetsMarkItems[0];
        $hasMarks = $firstMarkItem->mark != 0;
        echo "<h2 class='cathdr'>$categoryDesc</h2>\n";

        if ($displayType == 'FULL') {
            if ($hasMarks) {
                echo "<table class='categoryresults'><tr><th></th><th>Mark</th><th>Out of</th></tr>\n";
            } else {
                echo "<table class='categoryresults'><tr><th></th></tr>\n";
            }
        }
        else if ($displayType == 'PARTIAL') {
            if ($hasMarks) {
                echo "<table class='categoryresults'><tr><th></th><th>Assessment</th></tr>\n";
            } else {
                echo "<table class='categoryresults'><tr><th></th></tr>\n";
            }
        }

        $hdrIsDone = FALSE;

        foreach ($thisSheetsMarkItems as $markItem) {
            $comment = $markItem->commentOverride ? $markItem->commentOverride : $markItem->comment;
            $comment = str_replace('\_', '_', Markdown(format($comment), True));
            $weight = $markItem->weight;
            $maxMark = $markItem->mark;
            $mark = sprintf("%.2f", $maxMark * $weight);
            $maxMark = sprintf("%.1f", $maxMark);
            if ($displayType == 'NONE') {
                echo "<div class='comment'>$comment</div>\n";
            }
            elseif ($maxMark == 0) {
                echo "<tr><td colspan='3' class='noweight_comment'>$comment</td></tr>\n";
            }
            else if ($displayType == 'FULL') {
                echo "<tr><td class='commentcol'>$comment</td><td class='mark'>$mark</td><td class='outof'>$maxMark</td></tr>\n";
            }
            else {
                $category = $mark == $maxMark ? "Yes" : ($mark == 0 ? "No" : "Partial");
                echo "<tr><td class='commentcol'>$comment</td><td class='mark'>$category</td></tr>\n";
            }
        }

        if ($displayType != 'NONE') {
            echo "</table>\n";
        }

    }

    $customComment = $marksheet->comments;
    if ($customComment != '') {
        echo "<div class='customcomments'><h2 class='cathdr'>Further comments</h2>\n";
        $customCommentFmtd =  str_replace('\_', '_', Markdown(format($customComment), True));
        echo "$customCommentFmtd</div>\n";
    }

    if ($marksheet->bonus != 0 && $displayType != 'NONE') {
        echo "<div class='bonus-para'><p><span class='label'>Bonus/Penalty: </span>" .
            $marksheet->bonus . "</p></div>";
    }
    $mark = $marksheet->markTotal;
    $outOf = $assignment->outOf;
    $formattedMark = sprintf("%.2f", $mark);
    $formattedOutOf = sprintf("%.1f", $outOf);
    echo "<h2 class='totalmark'>Mark: $formattedMark / $formattedOutOf</h2>";
}
?>
</div>
