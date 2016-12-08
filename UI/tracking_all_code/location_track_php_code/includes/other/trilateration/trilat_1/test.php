<?php

error_reporting(E_ALL);

/*

xa = position_1_x
ya = position_1_y

xb = position_2_x
yb = position_2_y

xc = position_3_x
yc = position_3_y

ra = dist_to_pos_1
rb = dist_to_pos_2
rc = dist_to_pos_3

S = (pow(xc,2) - pow(xb,2) + pow(yc,2) - pow(yb,2) + pow(rb,2) - pow(rc,2)    ) / 2
T = (pow(xa,2) - pow(xb,2) + pow(ya,2) - pow(yb,2) + pow(rb,2) - pow(ra,2)    ) / 2

unknown_position_y = ((T*(xb-xc)) - (S*(xb-xa))) /  (((ya-yb)*(xb-xc)) - ((yc-yb)*(xb-xa)))

unknown_position_x = ((unknown_position_y*(ya-yb)) - T) / (xb-xa)

*/


$position_1_x = 50;
$position_1_y = 150;

$position_2_x = 100;
$position_2_y = 300;

$position_3_x = 200;
$position_3_y = 100;

$dist_to_pos_1 = 150;
$dist_to_pos_2 = 100;
$dist_to_pos_3 = 200;




$xa = $position_1_x;
$ya = $position_1_y;

$xb = $position_2_x;
$yb = $position_2_y;

$xc = $position_3_x;
$yc = $position_3_y;

$ra = $dist_to_pos_1;
$rb = $dist_to_pos_2;
$rc = $dist_to_pos_3;

$S = (pow($xc,2) - pow($xb,2) + pow($yc,2) - pow($yb,2) + pow($rb,2) - pow($rc,2)    ) / 2;
$T = (pow($xa,2) - pow($xb,2) + pow($ya,2) - pow($yb,2) + pow($rb,2) - pow($ra,2)    ) / 2;

$unknown_position_y = (($T*($xb-$xc)) - ($S*($xb-$xa))) / ((($ya-$yb)*($xb-$xc)) - (($yc-$yb)*($xb-$xa)));

$unknown_position_x = (($unknown_position_y*($ya-$yb)) - $T) / ($xb-$xa);





echo <<<EOHTML

<div style="position: relative; border: 1px solid #000000; height: 500px; width: 500px">

	<div style="position: absolute; top: 0; left: 50px; width: 1px; height: 500px; background-color: #FFCC00; text-align: center">50</div>
	<div style="position: absolute; top: 0; left: 100px; width: 1px; height: 500px; background-color: #FFCC00; text-align: center">100</div>
	<div style="position: absolute; top: 0; left: 150px; width: 1px; height: 500px; background-color: #FFCC00; text-align: center">150</div>
	<div style="position: absolute; top: 0; left: 200px; width: 1px; height: 500px; background-color: #FFCC00; text-align: center">200</div>
	<div style="position: absolute; top: 0; left: 250px; width: 1px; height: 500px; background-color: #FFCC00; text-align: center">250</div>
	<div style="position: absolute; top: 0; left: 300px; width: 1px; height: 500px; background-color: #FFCC00; text-align: center">300</div>
	<div style="position: absolute; top: 0; left: 350px; width: 1px; height: 500px; background-color: #FFCC00; text-align: center">350</div>
	<div style="position: absolute; top: 0; left: 400px; width: 1px; height: 500px; background-color: #FFCC00; text-align: center">400</div>
	<div style="position: absolute; top: 0; left: 450px; width: 1px; height: 500px; background-color: #FFCC00; text-align: center">450</div>

	<div style="position: absolute; top: 50px; left: 0; width: 500px; height: 1px; background-color: #FFCC00; text-align: center">50</div>
	<div style="position: absolute; top: 100px; left: 0; width: 500px; height: 1px; background-color: #FFCC00; text-align: center">100</div>
	<div style="position: absolute; top: 150px; left: 0; width: 500px; height: 1px; background-color: #FFCC00; text-align: center">150</div>
	<div style="position: absolute; top: 200px; left: 0; width: 500px; height: 1px; background-color: #FFCC00; text-align: center">200</div>
	<div style="position: absolute; top: 250px; left: 0; width: 500px; height: 1px; background-color: #FFCC00; text-align: center">250</div>
	<div style="position: absolute; top: 300px; left: 0; width: 500px; height: 1px; background-color: #FFCC00; text-align: center">300</div>
	<div style="position: absolute; top: 350px; left: 0; width: 500px; height: 1px; background-color: #FFCC00; text-align: center">350</div>
	<div style="position: absolute; top: 400px; left: 0; width: 500px; height: 1px; background-color: #FFCC00; text-align: center">400</div>
	<div style="position: absolute; top: 450px; left: 0; width: 500px; height: 1px; background-color: #FFCC00; text-align: center">450</div>

	<div style="position: absolute; top: {$position_1_y}px; left: {$position_1_x}px; width: 10px; height: 10px;" id="pos_1">1</div>
	<div style="position: absolute; top: {$position_2_y}px; left: {$position_2_x}px; width: 10px; height: 10px;" id="pos_2">2</div>
	<div style="position: absolute; top: {$position_3_y}px; left: {$position_3_x}px; width: 10px; height: 10px;" id="pos_3">3</div>
	<div style="position: absolute; top: {$unknown_position_y}px; left: {$unknown_position_x}px; width: 10px; height: 10px;" id="pos_unk">?</div>

	<div style="position: absolute; top:300px; left: 200px; width: 10px; height: 10px;" id="pos_unk">x</div>

</div>

EOHTML;

?>