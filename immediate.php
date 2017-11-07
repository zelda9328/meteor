<?php
    include("libs/baseSetting.php");
    $dateformat = "Y/m/d";
    $dbh->exec("use solar");
    
    $type = $_GET["type"];
    
    switch($type){
        case "init":
            $sql = "select factory.no,factory.cname,inverter.no as id,count(inverter.sn)as amount from inverter "
                . "join factory on inverter.appertain = factory.no group by factory.cname order by appertain,sn";
            $ps = $dbh->query($sql);
            $rs = $ps->fetchAll(PDO::FETCH_ASSOC);
            break;
        
        case "req":
            $sql = "select factory.no as no, cname, sn, stamp, voltageDC, voltageAC, currentDC, currentAC, tmp.wattage, todayWatt "
                . "from (select * from immediate where stamp >= :start and stamp < :end)tmp join inverter using(no) join factory on inverter.appertain = factory.no "
                . "where factory.no = :no order by stamp";
            $ps = $dbh->prepare($sql);
            $ps->execute(array(":start"=>$_GET["start"], ":end"=>$_GET["end"], ":no"=>$_GET["no"]));
            $rs=$ps->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
    
    $pack = json_encode($rs);
    echo $pack;
?>