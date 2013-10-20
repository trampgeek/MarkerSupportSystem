<?php
echo "username,marker,name,mark<br />";
foreach ($marks as $row) {
    $line = $row['username'] . ',' . $row['marker'] . ',' . $row['name'] . ',' . $row['mark'];
    if (isset($row['error'])) {
        $line .= ',' . $row['error'];
    }
    echo $line . "<br />\n";
}
include('footer.php');
