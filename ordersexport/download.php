<?php

header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"../upload/export.csv\""); 
readfile("../upload/export.csv");
exit;
?>