<?php 
include("handlecsv.php");

$fileName = "1_on_60";

$import = new handleCSV($fileName);
$import->import_all();

