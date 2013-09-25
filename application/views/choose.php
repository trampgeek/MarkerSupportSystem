<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<script type="text/javascript">
    function doSubmit() {
        var f = document.forms[0];
        f.submit();
    }
</script>
<div class='choose'>
    <h1>Choose Student</h1>
    <p>Note: the mark shown here is the value computed when the marksheet was last
        saved by a marker. It is zero for unmarked assignment and is not updated by
        changes to the assignment settings. If you want totally dependable marks,
        view the marklogs or export the marks.</p>
    <p>
    <div class="student-list-div" id='selected-list-div'>
    <table class="student-list-table">
        <?php
        $rowNum = 0;
        $selectedRow = 0;
        foreach ($marksheets as $sheet) {
            $selected = $sheet->studentId == $selectedStud ? " class='selected'" : '';
            if ($selected) {
                $selectedRow = $rowNum;
            }
            if ($sheet->markerId) {
                 $marker = $markers->getMarkerUsername($sheet->markerId);
            } else {
                 $marker = "&lt;unmarked&gt;";
            }

            ?>
            <tr<?php echo $selected; ?>>
                <td><a href="<?php echo site_url() . "/marker/mark/$sheet->studentId"?>">Mark</a></td>
                <td><?php echo $sheet->username; ?></td>
                <td><?php echo $sheet->name; ?></td>
                <td><?php echo sprintf("%.2f", $sheet->markTotal); ?></td>
                <td><?php echo $marker; ?></td>
                <td><a href="<?php echo site_url() . "/marker/showMarkLog/$sheet->studentId"?>">View marklog</a></td>
            </tr>
            <?php
            $rowNum += 1;
        }

        ?>
    </table>
    </div>
    <script type="text/javascript">
        var selectedDiv = $('#selected-list-div');
        var rowHeight = $('.student-list-table tr:first-child').height();
        selectedDiv.scrollTop(<?php echo max(0, ($selectedRow - 5)); ?> * rowHeight);
    </script>
</p>
</div>
<?php include('footer.php'); ?>
