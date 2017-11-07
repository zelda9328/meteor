<?php
    include("libs/baseSetting.php");
    $dateformat = "Y/m/d";
    $dbh->exec("use solar");
    
    $id = $_GET["id"];
    $querypow =  "select power from historyPow where no =:id and date between :date1 and :date2";
    
    $ps = $dbh->prepare($querypow);
    $d1 = date($dateformat);
    $d2 = date($dateformat, strtotime("+1 day") );
    $ps->execute(array(":id"=>$id, ":date1"=>$d1, ":date2"=>$d2));
    $rs = $ps->fetchAll(PDO::FETCH_ASSOC);
    
    $returnData = json_encode($rs);
    echo $returnData;
?>