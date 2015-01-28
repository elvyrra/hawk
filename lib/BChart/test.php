<?php

include "BChart.class.php";
include "BLineChart.class.php";

$chart = new BLineChart(array(
   "id" => "test",
   "data" => array(
        "2012-02-01" => array("time" => "2012-02-01", "value" => 1000, "t" => 5000),
        "2012-02-21" => array("time" => "2012-02-21", "value" => 5000, "t" => 25000 ),
        "2012-03-10" => array("time" => "2012-03-10", "value" => 19000, "t" => 45000),
        "2012-03-01" => array("time" => "2012-03-01", "value" => 25000, "t" => -15000),
        "2012-03-05" => array("time" => "2012-03-05", "value" => 12000),
        "2012-03-25" => array("time" => "2012-03-25", "value" => 23000, "t" => 12000),
        "2012-04-06" => array("time" => "2012-04-06", "value" => 8000, "t" => 8000),
        "2012-05-01" => array("time" => "2012-05-01", "value" => 32000),
        "2012-08-15" => array("time" => "2012-08-15", "value" => 26000, "t" => 29000),
    ),
    "height" => 500,
    "width" => 800,    
    "ykeys" => array("value", "t"),    
	"yscale" => "dynamic",
    // "smooth" => false
));

$chart->display();
