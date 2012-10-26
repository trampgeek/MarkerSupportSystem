<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class='marklog'>
<h1>Summary of assignment marks for <?php echo "{$marksheet->studentName} ({$marksheet->username})" ?></h1>

<?php
    if ($marksheet->partnerUsername) {
        $partnerName = $marksheet->getFullName($marksheet->partnerUsername);
        echo "<p class='comment'>Your partner is $partnerName</p>";
    }

    if ($marksheet->marker == '') {
        echo "<p class='comment'>Marking is not yet complete.</p>";
    }
    else {
        $styleMark = $marksheet->markTotal;
        $styleFactorFormatted = sprintf("%.3f", $styleMark);
        $correctnessMark = $marksheet->rawCorrectness;
        $correctnessFormatted = sprintf("%.1f", $correctnessMark);
        echo "<p>Raw correctness: $correctnessFormatted/35.0</p>";
        echo "<p>Style factor: $styleFactorFormatted</p>";
        $latenessPenalty = $marksheet->latenessPenaltyPercent;
        $total = $correctnessMark * (1 + $styleMark * 15.0 / 35.0);
        echo "<p>Total = " . sprintf("%.1f", $total) . "/50  (see assignment spec for formula used)</p>";
		if ($guiMark != 0) {
			echo "<p>GUI step bonus mark: $guiMark/3</p>";
			$total += floatval($guiMark);
			echo "<p>Total including bonus = " . sprintf("%.1f", $total) . "/50</p>";
		}
        if ($latenessPenalty != 0) {
			echo "<p>Lateness penalty: $latenessPenalty%</p>";
			$total *= (1 - $latenessPenalty / 100.0);
		}

        $formattedMark = sprintf("%.1f", $total);
        echo "<h2 class='totalmark'>Final mark: $formattedMark/50.0</h2>";
    }
?>
</div>

