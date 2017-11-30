<?php
/*
 * 這邊假定發電廠站台的inverter id都是連續的
 */
    include("libs/baseSetting.php");
    $dateformat = "Y/m/d";
    $dbh->exec("use solar");
    
    $type = $_GET["type"];
    //$type = "init";
    switch($type){
        case "init":
            $rsSet = array();
            $sql = "select no,cname from factory order by no";                
            $ps = $dbh->query($sql);
            while($rs = $ps->fetch(PDO::FETCH_ASSOC)){
                $factoryArray = array();
                $factoryArray["no"] = $rs["no"];
                $factoryArray["cname"] = $rs["cname"];
                
                $idArray = array();
                $sql = "select sn from inverter "
                    . "join factory on inverter.appertain=factory.no where appertain = :no";
                $psInv = $dbh->prepare($sql);
                $psInv->execute(array("no"=>$rs["no"]));

                while($rs1 = $psInv->fetch()){
                    $idArray[] = $rs1[0];
                }
                $factoryArray["ids"] = $idArray;
                
                $rsSet[] = $factoryArray;
            }
            break;
        
        case "req":
            $sql = "select factory.no as no, cname, sn, stamp, voltageDC, voltageAC, currentDC, currentAC, tmp.wattage, todayWatt "
                . "from (select * from immediate where stamp >= :start and stamp < :end)tmp join inverter using(no) join factory on inverter.appertain = factory.no "
                . "where factory.no = :no order by stamp";
            $ps = $dbh->prepare($sql);
            $ps->execute(array(":start"=>$_GET["start"], ":end"=>$_GET["end"], ":no"=>$_GET["no"]));
            $rsSet=$ps->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
    
    $pack = json_encode($rsSet);
    echo $pack;        
?>