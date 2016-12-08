<?php

/* * * * * * * * * * * * * * * * * * * * * *
 *  NOTE!  I normally wouldn't use <div>s  *
 *  to create graphs, but I assumed it was *
 *  what you wanted.                       *
 * * * * * * * * * * * * * * * * * * * * * */

function tril_table($p){  // print the table of points
	$i = 0;
	$end = 3;  // last value
	
	echo
<<<EOHTML
	<TABLE style="margin: 1px; padding: 0px; border: 1px solid #000000;">
		<TR>
			<TD></TD>
			<TD><B>Coordinate (X, Y)</B></TD>
			<TD><B>Inputted DFC</B></TD>
			<TD><B>Graphed DFC</B></TD>
		</TR>

EOHTML;
for($i = 0;  $i < 4;  $i++){
	if($i==$end)
		$n = "s";
	else
		$n = "p". ($i+1);
		echo
<<<EOHTML
		<TR>
			<TD><B>$n</B></TD>
			<TD>({$p[$i]->getX()}, {$p[$i]->getY()})</TD>
			<TD>{$p[$i]->getDFC()}</TD>
			<TD>{$p[$i]->getDist($p[$end])}</TD>
		</TR>
EOHTML;

	}

	echo
<<<EOHTML
	</TABLE>
EOHTML;
}

function tril_dgraph($graph){
	$h = $graph["height"];
	$w = $graph["width"];
	$hl = $graph["height_lines"];
	$wl = $graph["width_lines"];
	$hs = $graph["height_size"];
	$ws = $graph["width_size"];

	echo '<div style="
			position: relative;
			border: 1px solid #000000;
			height: '. $h . 'px;
			width: '. $w .'px
		">' . "\n"; // graph container

	$hi = $h/($hl + 1);  // Pixels Per Height Interval
	$wi = $w/($wl + 1);  // Pixels Per Width Interval

	$bgc = "#FFCC00"; // color of lines

	for($i = 0;  $i < $wl;  $i++){ // print the vertical lines

		echo '<div style="
				position: absolute;
				top: 0;
				left: '. ($i + 1) * $wi .'px;
				width: 1px;
				height: ' . $h . 'px;
				background-color: ' . $bgc . ';
				"></div>' . "\n";
	}

	for($i = 0;  $i < $hl;  $i++){ // print the horizontal lines

		echo '<div style="
				position: absolute;
				top: ' . ($i + 1) * $hi . 'px;
				left: 0px; width: ' . $w . 'px;
				height: 1px;
				background-color: ' . $bgc . ';
			"></div>' . "\n";
	}
	
	// SEE 4 QUADRANT CODE 1 AT BOTTOM OF THE PAGE IF YOU WANT TO REVERT TO 4 QUADRANTS
	
	$bgc = "#000000";

	
	for($i = 0;  $i <= $hl + 1;  $i++){ // Plot Y-Axis Relative Values
		if($i%2 == 1){  // every 2
			continue;
		}
			echo '
	<div style="
		position: absolute;
		top: '  . ($i * $h/($hl+1) - 10) . 'px;
		left: ' . -40 . 'px;
		align: left;
		width: 10px;
		height: 10px;
		font-size:70%;
	">	
		'; 
		printf("%1.2f",$i * $hs/($hl+1));  // floating point 0.00 accuracy
	   echo	'
		</div>' . "\n";
	}

	for($i = 0;  $i <= $wl + 1;  $i++){  // Plot X-Axis Relative Values
		if($i%2 == 1){  // every 2
			continue;
		}
			echo '
	<div style="
		position: absolute;
		top: '  . -30 . 'px;
		left: ' . ($i * $w /($wl+1)) . 'px;
		width: 10px;
		height: 10px;
		font-size:70%;
	">
		';
		printf("%1.2f",$i * $ws/($wl+1)); // floating point 0.00 accuracy
			echo '
		</div>' . "\n";
	}

	// END NEW CODE
	
	return;
}

function tril_egraph($graph){
	echo '</div>' . "\n"; // simply end the graph container
	return;
}
 
function tril_plot($coord, $graph, $name="", $bg_img = "./point.png"){
	list($img_w, $img_h, $img_type, $img_attrib) = getimagesize($bg_img);
	$h = $graph["height"];
	$w = $graph["width"];

	$hl = $graph["height_lines"];
	$wl = $graph["width_lines"];

	$hs = $graph["height_size"];
	$ws = $graph["width_size"];

	$wpi = $w/($wl+1);
	$hpi = $h/($hl+1);
	
	$xco = (($coord->getX()) * $wpi * (1/($ws/($wl+1))));  // raw x coordinate in pixels
	$yco = (($coord->getY()) * $hpi * (1/($hs/($hl+1))));  // raw y coordinate in pixels

//	echo "<div style = \"font-size:75%;\"><BR>xco: $xco; wpi: $wpi<BR>";
//	echo "yco: $yco; hpi: $hpi<BR></div>";

//	$xco += $w/2 - $img_w/2 ;  // fixed x coord in pixels (4 q graph)
//	$yco += $h/2 - $img_h/2 ;  // fixed y coord in pixels (4 q graph)


	$xco -=$img_w/2 ;  // fixed x coord in pixels
	$yco -=$img_h/2 ;  // fixed y coord in pixels

//	echo "($xco,$yco)<BR>\n";

	echo '
	<div style="
		position: absolute;
		top: '  . $yco . 'px;
		left: ' . $xco . 'px;
		width: 10px;
		height: 10px;
	">	
		<img style="
			text-align:center;
			vertical-align:middle;
			margin:0 auto;
			"
			src="
			' . $bg_img . '
			"
			width=100%
			height=100%
		><B>'. $name .'</B>
	</div>' . "\n";
}



// 4 QUADRANT CODE 1
	/*
	
	$bgc = "#000000";

	echo '<div style="
				position: absolute;
				top: 0;
				left: '. $w/2 .'px;
				width: 1px;
				height: ' . $h . 'px;
				background-color: ' . $bgc . ';	
			"></div>' . "\n"; // Y-Axis
	
	
	echo '<div style="
				position: absolute;
				top: ' . $h/2 . 'px;
				left: 0px;
				width: ' . $w . 'px;
				height: 1px;
				background-color: ' . $bgc . ';
				"></div>' . "\n";  // X-Axis

	
	for($i = 0;  $i <= $hl + 1;  $i++){ // Plot Y-Axis Relative Values
		if($i%2 == 1){  // every 2
			continue;
		}
			echo '
	<div style="
		position: absolute;
		bottom: '  . ($i * $h/($hl+1) - 10) . 'px;
		left: ' . $w/2 . 'px;
		width: 10px;
		height: 10px;
		font-size:70%;
	">	
		'; 
		printf("%1.2f",$i * $hs/($hl+1) - $hs/2);  // floating point 0.00 accuracy
	   echo	'
		</div>' . "\n";
	}

	for($i = 0;  $i <= $wl + 1;  $i++){  // Plot X-Axis Relative Values
		if($i%2 == 1){  // every 2
			continue;
		}
			echo '
	<div style="
		position: absolute;
		bottom: '  . ($h/2 - 10) . 'px;
		left: ' . $i * $w /($wl+1) . 'px;
		width: 10px;
		height: 10px;
		font-size:70%;
	">
		';
		printf("%1.2f",$i * $ws/($wl+1) - $ws/2); // floating point 0.00 accuracy
			echo '
		</div>' . "\n";
	}
	 */
?>


