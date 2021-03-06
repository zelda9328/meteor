<?php
/*
 * 除了做氣象站查詢鄉鎮地方之外
 * 還有太陽能歷史發電查詢
 */
    include("libs/TaskEnum.php");
    include("libs/baseSetting.php");
    
    define(def_dateFormat, "m/d");

    openlog("query", null, LOG_LOCAL1);
    
    $type = $_GET["type"];
    //$type = "init";
    //syslog(LOG_INFO, "type is " . $type);
    $queryArea = "select no,area as name from area";
    $queryCity = 'select no,city as name from city join station on city.no=station.cityNo where areaNo=:area and station.id like "4%"';
    $queryStation = "select id as no,name from station where cityNo=:city";

    //封裝成json的來源
    $pack = array();
    switch($type){
        case "init":
            $task = TaskEnum::init;
            break;
        case "area":
            $task = TaskEnum::area;
            break;
        case "city":
            $task = TaskEnum::city;
            break;
        case "station":
            $task = TaskEnum::station;
            break;
        case "submit":
            $task = TaskEnum::submit;
            break;
        default:
            $task = 0;
    }

    if($task >= TaskEnum::init){
        $ps = $dbh->query($queryArea);
        $rsArea = $ps->fetchAll(PDO::FETCH_ASSOC);
        $pack[] = $rsArea;
        
        //solar query
        /*
         * 放棄使用id控制資料源，以下mark
         */
        /*
        $solar = array();
        $ps = $dbh->query("select solar.factory.no as factoryNo,solar.factory.cname from solar.factory "
                . "order by solar.factory.no");
        while($row = $ps->fetch(PDO::FETCH_ASSOC)){
            
            $psInner = $dbh->prepare("select solar.inverter.no, solar.inverter.sn from solar.inverter "
                    . "join solar.factory on solar.inverter.appertain=solar.factory.no "
                    . "where solar.factory.no = :no order by solar.inverter.sn");
            $psInner->execute(array(":no"=>$row["factoryNo"]));
            $no = array();
            $sn = array();
            while($rowInner = $psInner->fetch(PDO::FETCH_ASSOC)){
                $no[] = $rowInner["no"];
                $sn[] = $rowInner["sn"];
            }
            $arrayObj = array();
            $arrayObj["no"] = $row["factoryNo"];
            $arrayObj["cname"] = $row["cname"];
            $arrayObj["inverterkey"] = $no;
            $arrayObj["id"] = $sn;
            $solar[] = $arrayObj;
        }
         
         */
    }
    if($task >= TaskEnum::area){
        //初值是拿資料庫的第一筆，如果有傳值則替代
        $areaValue = $rsArea[0]["no"];
        if($task == TaskEnum::area && isset($_GET["area"])){
            $areaValue = $_GET["area"];
        }

        $ps = $dbh->prepare($queryCity);
        $ps->execute(array(":area" => $areaValue));
        $rsCity = $ps->fetchAll(PDO::FETCH_ASSOC);
        $pack[] = $rsCity;
    }
    if($task >= TaskEnum::city){
        $cityValue = $rsCity[0]["no"];
        if($task == TaskEnum::city && isset($_GET["city"])){
            $cityValue = $_GET["city"];
        }
        $ps = $dbh->prepare($queryStation);
        $ps->execute(array(":city" => $cityValue));
        $rsStation = $ps->fetchAll(PDO::FETCH_ASSOC);
        $pack[] = $rsStation;
    }
    if($task == TaskEnum::submit){
        $srcTime1 = $_GET["start"];
        $srcTime2 = $_GET["end"];
        $firstDate = strtotime($srcTime1);
        $overDate = strtotime("1 day",strtotime($srcTime2));
        //syslog(LOG_INFO, "time1 is " . $firstDate);
        //syslog(LOG_INFO, "tim2 is " . $overDate);
        if($firstDate==false || $overDate==false)   die();
        
        $sql = "select calendar,sun from messureByDay where id = :id and calendar >= :start and calendar < :end order by calendar";
        $sqlPre = $dbh->prepare($sql);

//$_GET["station"] = 466880;
        $innArr = array(":id" => $_GET["station"],":start" => date("Y-m-d", $firstDate),":end" => date("Y-m-d", $overDate));
        $sqlPre->execute($innArr);
        //$rs = $dbh->query("show warnings");
        $row = $sqlPre->fetchAll(PDO::FETCH_ASSOC);
        //var_dump($row);
        $rowSun = array();
        $rowCal = array();
        foreach($row as $r1){
            $rowSun[] = $r1["sun"];
            $rowCal[] = date(def_dateFormat, strtotime($r1["calendar"]));
        }


        //依照chart.js規範做成資料源
        $dataY = array("data" => $rowSun);
        $member = array($dataY);
        //$chartData = array("labels" => $month);
        //$chartData["datasets"] = $member;
        /*
        $data1 = '{type: submit},
        {labels : ["January","February","March","April","May","June","July"],
           datasets : [{backgroundColor: window.chartColors.red,
                        label:"first",
                        data : [65,59,90,-81,56,55,-40]
                        },
                    {   backgroundColor: window.chartColors.red,
                        borderColor: window.chartColors.blue,
                        label:"second",
                        data : [28,48,40,19,96,27,100]
                        }]}';
        //$json = json_encode($data);
         */
        $data["labels"] = $rowCal;
        $data["datasets"] =$member;
        $pack[] = $data;
        /*
        $getSolarPower_sql = "select power,date from solar.historyPow "
                . "where no=:station and date >= adddate(:start, interval 1 day) "
                . "and date < adddate(:end, interval 1 day) order by date";
        $sqlPre = $dbh->prepare($getSolarPower_sql);
        $innArr = array(":station" => $_GET["inverterNo"],":start" => date("Y-m-d", $firstDate),":end" => date("Y-m-d", $overDate));
         * 
         */
        /*
        ob_start();
        var_dump($innArr);
        $obCon = ob_get_contents();
        syslog(LOG_INFO,"get var dump: " . $obCon);
        syslog(LOG_INFO,"sql: " . $getSolarPower_sql);
        ob_end_clean();
        */
        /*
        $sqlPre->execute($innArr);
        $row = $sqlPre->fetchAll(PDO::FETCH_ASSOC);
        
        $powerArray = array();
        $power_timestamp = array();
        foreach($row as $r){
            $powerArray[] = $r["power"];
            $stamp = strtotime($r["date"]);
            $power_timestamp[] = date(def_dateFormat, strtotime("-1 day", $stamp));
        }
        
        $solardataY = array("data" => $powerArray);
        $member = array($solardataY);
         
        $solardata["datasets"] = $member;
        $solardata["labels"] = $power_timestamp;
         * 
         */
        //$pack[] = $solardata;
        $pack[] = queryHistoryPow($_GET["solar_factory"], $firstDate, $overDate);
        
        //closelog();
    }
    
    if($task == 0){        
        
    }
    if(isset($solar)){
        $pack[] = $solar;
    }

    $pack[] = $type;

    $pk = json_encode($pack);
    echo $pk;
    //die();
    
    function queryHistoryPow($no, $firstDate, $overDate){
        global $dbh;
        $dbh->exec("use solar");
        $sql = "SELECT cname,date,sn,power FROM historyPow "
                . "join inverter using (no) join factory on inverter.appertain=factory.no "
                . "where factory.no=:no and date >=adddate(:start, interval 1 day) and date <adddate(:end, interval 1 day) "
                . "order by date";
        $ps = $dbh->prepare($sql);
        $ps->execute(array(":no"=>$no, ":start"=>date("Y-m-d", $firstDate), ":end"=>date("Y-m-d", $overDate)));
        $rs = $ps->fetchAll(PDO::FETCH_ASSOC);
        ob_start();
        var_dump($rs);
               $obCon = ob_get_contents();
        syslog(LOG_INFO,"get var dump: " . $obCon);        
        ob_end_clean();
        $dbh->exec("use meteor");
        return $rs;
    }
?>
