/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
window.chartColors = {
    red: 'rgb(255, 0, 0)',
    orange: 'rgb(255, 128, 0)',
    yellow: 'rgb(255, 255, 0)',
    green: 'rgb(0, 255, 0)',
    blue: 'rgb(0, 0, 255)',
    purple: 'rgb(153, 102, 255)',
    grey: 'rgb(201, 203, 207)'
};
window.colorSet = [window.chartColors.red, window.chartColors.green, window.chartColors.blue,
                window.chartColors.purple, window.chartColors.yellow, window.chartColors.orange];

//chart config encapsulation
function ChartConfig(xydataset){
    this.data = xydataset;
}
ChartConfig.prototype.type = "line";
ChartConfig.prototype.chartName = function(name){
    this.options = {title:{
            display:true,
            text:name
    }};
}

function XYDataSet(Xarray){
    this.labels = Xarray;
    this.datasets = new Array();
}
XYDataSet.prototype.push = function(name, Yarray, color){
    this.datasets.push({
        label:name,
        data:Yarray,
        backgroundColor:color,
        borderColor:color,
        fill:false
        });
}

//逆變器資料封裝
function Packing(id, color){
    this.id = id;
    this.color = color;
    this.voltageDC = new Array();
    this.voltageAC = new Array();
    this.currentDC = new Array();
    this.currentAC = new Array();
    this.wattage = new Array();
    this.todayWatt = new Array();
}
Packing.prototype.push = function(property, value){
    this[property].push(value);
}
Packing.prototype.clear = function(){
    for(var key in propertySet){
        this[propertySet[key]].length = 0;
    }
}

function initFactory(){
    let url = "immediate.php?type=init";
    let data;
    $.ajax({
        async:false,
        url:url,
        type:"get",
        datatype:"json",
        success:function(msg){
            data = msg;
        }
    });
    return data;
}