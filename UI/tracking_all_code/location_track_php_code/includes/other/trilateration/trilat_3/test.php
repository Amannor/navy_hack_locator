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







$position_1_x = 50;
$position_1_y = 150;

$position_2_x = 100;
$position_2_y = 300;

$position_3_x = 200;
$position_3_y = 100;

$dist_to_pos_1 = 150;
$dist_to_pos_2 = 100;
$dist_to_pos_3 = 200;







$x = array(0, 0, 0);
$y = array(0, 0, 0);

$x[0] = $position_1_x;
$y[0] = $position_1_y;

$x[1] = $position_2_x;
$y[1] = $position_2_y;

$x[2] = $position_3_x;
$y[2] = $position_3_y;

$r[0] = $dist_to_pos_1;
$r[1] = $dist_to_pos_2;
$r[2] = $dist_to_pos_3;

$Intersectx = array(0, 0, 0, 0, 0, 0);
$Intersecty = array(0, 0, 0, 0, 0, 0);
// intersection points between a and b
$delta_x = $x[0] -$x[1];
$delta_y = $y[0] -$y[1];
$delta2 =  pow($delta_x,2) + pow($delta_y,2);
$delta = sqrt($delta2);

$s = ($delta2 + pow($r[0],2)-pow($r[1],2))/(2*$delta);
$cx = $x[0] + $delta_x*$s/$delta;
$cy = $y[0] + $delta_y*$s/$delta;

$u = sqrt(pow($r[0],2) -pow($s,2)) ;
$Intersectx[0] = $cx - $delta_y*$u/$delta;
$Intersecty[0] = $cy + $delta_x*$u/$delta;

$Intersectx[1] = $cx + $delta_y*$u/$delta;
$Intersecty[1] = $cy - $delta_x*$u/$delta;

// intersection points between a and c
$delta_x = $x[0] -$x[2];
$delta_y = $y[0] -$y[2];
$delta2 =  pow($delta_x,2) + pow($delta_y,2);
$delta = sqrt($delta2);

$s = ($delta2 + pow($r[0],2)-pow($r[2],2))/(2*$delta);
$cx = $x[0] + $delta_x*$s/$delta;
$cy = $x[2] + $delta_y*$s/$delta;

$u = sqrt(pow($r[0],2) -pow($s,2)) ;
$Intersectx[2] = $cx - $delta_y*$u/$delta;
$Intersecty[2] = $cy + $delta_x*$u/$delta;

$Intersectx[3] = $cx + $delta_y*$u/$delta;
$Intersecty[3] = $cy - $delta_x*$u/$delta;


// intersection points between c and b
$delta_x = $x[1] -$x[2];
$delta_y = $y[1] -$y[2];
$delta2 =  pow($delta_x,2) + pow($delta_y,2);
$delta = sqrt($delta2);

$s = ($delta2 + pow($r[1],2)-pow($r[2],2))/(2*$delta);
$cx = $x[1] + $delta_x*$s/$delta;
$cy = $x[2] + $delta_y*$s/$delta;

$u = sqrt(pow($r[1],2) -pow($s,2)) ;
$Intersectx[4] = $cx - $delta_y*$u/$delta;
$Intersecty[4] = $cy + $delta_x*$u/$delta;

$Intersectx[5] = $cx + $delta_y*$u/$delta;
$Intersecty[5] = $cy - $delta_x*$u/$delta;

$distance = array();
$min1 = pow(($Intersectx[0] - $Intersectx[1]), 4) + pow(($Intersecty[0] - $Intersecty[1]), 4);
$counter = 0;

for ($idx1 = 0; $idx1 < 6; $idx1 += 1) {
    for ( $idx2 = 0; $idx2 < 6; $idx2 += 1) {
        if ($idx1 != $idx2){
                $counter = $counter +1;
                $distance[$counter] = pow(($Intersectx[$idx1] - $Intersectx[$idx2]), 2) + pow(($Intersecty[$idx1] - $Intersecty[$idx2]), 2);
                    if ($distance[$counter] < $min1) {
                    $x1 =  $Intersectx[$idx1];
                    $y1 =  $Intersecty[$idx1];
                    $x2 =  $Intersectx[$idx2];
                    $y2 =  $Intersecty[$idx2];
                    }
                }
        }
    }

$cent_y = ($y1+$y2)/2;
$cent_x = ($x1+$x2)/2;


// add another point to cluster
$min1 = pow(($cent_x - $Intersectx[0]), 2) + pow(($cent_y - $Intersecty[0]), 2);
    for ($idx1 = 0; $idx1 < 6; $idx1 += 1) {
        $distance = pow(($Intersectx[$idx1] - $cent_x), 2) + pow(($Intersecty[$idx1] - $cent_y), 2);
            if ($distance <= $min1) {
                $xx =  $Intersectx[$idx1];
                $yy =  $Intersecty[$idx1];
            }
    }
$cent_y = (2*$cent_y + $yy)/(3);
$cent_x = (2*$cent_x + $xx)/(3);


$unknown_position_y = $cent_y;
$unknown_position_x = $cent_x;
echo $unknown_position_y."<br />\n";
echo $unknown_position_x;


// find the closest 2 points
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