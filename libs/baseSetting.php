<?php
    date_default_timezone_set("Asia/Taipei");    
    $dsn = "mysql:host=localhost;dbname=meteor;charset=utf8";
    $dbh = new PDO($dsn, "root", "");
    $dbh->exec("set names utf8");
?>

