<?php 
include("handleCSV.php");

$fileName = "Products_half_quarter1";

$import = new handleCSV($fileName);
$import->import_all();

