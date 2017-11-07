<?php
$a = strtotime("2016-1");
$b = strtotime("2017-1");
var_dump($a);
var_dump($b);
echo date("Y-m-d", $a);
echo date("Y-m-d", $b);
?>
