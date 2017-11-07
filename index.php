<?php
    $dsn = "mysql:host=localhost;dbname=meteor;charset=utf8";
    $dbh = new PDO($dsn, "root", "");
    $dbh->exec("set names utf8");

    $sql = "select area from area";
    $sqlPre = $dbh->prepare($sql);
    $sqlPre->execute();
    $area = $sqlPre->fetchAll();

    //$sql = "select city from city join area on city.areaNo=area.no where area.area=:area";
    $sql = 'select city from city join area on city.areaNo=area.no join station on city.no=station.cityNo where area.area=:area and station.id like "4%"';
    $sqlPre = $dbh->prepare($sql);
    $sqlPre->execute(array("area"=>$area[0][0]));
    $city = $sqlPre->fetchAll();
    //print_r($city);


    $sql = "select name as station from station join city on station.cityNo=city.no join area on city.areaNo=area.no where area.area=:area and city.city=:city";
    $sqlPre = $dbh->prepare($sql);
    $sqlPre->execute(array("area"=>$area[0][0], "city"=>$city[0][0]));
    $station = $sqlPre->fetchAll();
    //while($row = $sqlPre->fetch()){
        //print_r($row);
    //}
?>

<!doctype html>
<html>
    <head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.js"></script>
        <script>
            var chart = null;
            function funcA(callType){
                console.log($("#area").val());
                console.log($("#city").val());
                console.log($("#station").val());
                //alert("hello");
                //$("#a1").val("hello");
                var url = "queryCity.php?type=".concat(callType);
                //alert(url);
                $.ajax({
                    url:url,
                    data:$("#form").serialize(),
                    
                    type:"get",
                    datatype:"json",

                    success:function(msg){
                        var obj = JSON.parse(msg);
                        console.log(msg);
                        console.log(obj);
                        //str = JSON.stringify(obj);
                        //console.log(str);
                        if(obj["type"] == "area"){

                            $("#city option").remove();
                            for(var key in obj){
                                if(key == "type") continue;
                                var op = new Option(obj[key].city,obj[key].city);
                                $("#city").append(op);
                                //alert(window);
                            }
                        } else if(obj["type"] == "city"){
                            $("#station option").remove();
                            for(var key in obj){
                                if(key == "type") continue;
                                var op = new Option(obj[key].name,obj[key].id);
                                $("#station").append(op);
                            }
                        } else  if(obj["status"] == "302"){
                        <!--
                            location.href = obj["location"];
                            -->
                        } else if(obj["type"] == "submit"){
                        console.log("enter submit");
                            var json = obj["chart"];
                            //var parse = JSON.stringify(json1);
                            //console.log(parse);
                            //alert(json1["datasets"]);

                               char = new Chart($("#chart"), {type: 'line', data: json});
                        }
                    }
                    
                }).then(function(data1){
                        console.log(data1);
                    });
            }
            $().ready(function(){
            });
        </script>
    </head>

    <body>
        <form id="form">
            <select name="area" id="area" onchange="funcA('area')">
            <?php
                foreach($area as $arr){
                    echo '<option value=' . $arr["area"] . '>' . $arr["area"] . '</option>';
                }
            ?>
            </select>
            <select name="city" id="city" onchange="funcA('city')">
            <?php
                foreach($city as $arr){
                    echo '<option value=' . $arr["city"] . '>' . $arr["city"] . '</option>';
                }
            ?>
            </select>
            <select name="station" id="station">
            <?php
                foreach($station as $arr){
                    echo '<option value=' . $arr["station"] . '>' . $arr["station"] . '</option>';
                }
            ?>
            </select>
            <!--
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            -->
            </select>
            <input id="a1" type="button" value="submit" onclick="funcA('submit')"/>
        </form>
        <br>
        <div style="width:600px;height:600px">
            <canvas id="chart"></csnvas>
        </div>
        <div style="width:600px;height:600px">
            <canvas id="chart1"></csnvas>
        </div>
    </body>
</html>
