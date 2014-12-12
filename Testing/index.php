<?php
include('handleCSV.php');
set_time_limit(0);
$file = 'products';

$reader = new handleCSV($file);

echo $reader -> run();