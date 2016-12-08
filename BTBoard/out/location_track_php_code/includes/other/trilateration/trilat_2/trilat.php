<?php

require_once('./trilat_coordinate.php'); // the Coordinate & dfcCoordinate class
require_once('./trilat_graph.php'); // the Coordinate & dfcCoordinate class

error_reporting(E_ALL);

function trilat_rotate($p){  // switch around the p[] array to possibly fit the formula
	$coord = $p[0];
	$p[0] = $p[1];
	$p[1] = $p[2];
	$p[2] = $coord;
	return;
}

function trilat_calc($p){  //  you are not expected to understand how this works.  (formula from the php sheet you sent.)
	$S = (
		  pow($p[2]->getX() , 2) - pow($p[1]->getX() , 2 )
		+ pow($p[2]->getY() , 2) - pow($p[1]->getY() ,2 )
		+ pow($p[1]->getDFC() , 2) - pow($p[2]->getDFC() , 2)
		) / 2;

	$T = (
		    pow($p[0]->getX() , 2) - pow($p[1]->getX() , 2) 
		  + pow($p[0]->getY() , 2) - pow($p[1]->getY() , 2) 
		  + pow($p[1]->getDFC() , 2) - pow($p[0]->getDFC() ,2)
		) / 2;


	$s = new dfcCoordinate(0,0,0);  // solution

	$div = (
				  (($p[0]->getY() - $p[1]->getY()) * ($p[1]->getX() - $p[2]->getX()))
				- (($p[2]->getY() - $p[1]->getY()) * ($p[1]->getX() - $p[0]->getX()))
			);

	if($div == 0){  // divison by 0
		$div = 1;
/*		
 		echo "<B>Math error; divison by 0:  resetting solution coordinates to (0,0).</B><BR/>\n";
		$s->setX(0);
		$s->setY(0);
		return $s;
 */
	}

	$s->setY(
			(
				  ($T * ($p[1]->getX() - $p[2]->getX()))
				- ($S * ($p[1]->getX() - $p[0]->getX()))
			)
			/
			$div
		);

	$div = (
				  $p[1]->getX()
				- $p[0]->getX()
			);

	if($div == 0){  // divison by 0
		$div = 1;
		/*
		echo "<B>Math error; divison by 0:  resetting solution coordinates to (0,0).</B><BR/>\n";
		$s->setX(0);
		$s->setY(0);
		return $s;
		 */
	}

	$s->setX(
			(
				  ($s->getY() * ($p[0]->getY() - $p[1]->getY())) - $T
			)
			/
			$div
		);
	

	return $s;
}


// Input page.  Make sure all the fields are filled.
if(!isset($_POST['x0']) || !isset($_POST['y0']) || !isset($_POST['d0']) ||
	!isset($_POST['x1']) || !isset($_POST['y1']) || !isset($_POST['d1']) ||
	!isset($_POST['x2']) || !isset($_POST['y2']) || !isset($_POST['d2']) ||
	!isset($_POST['g_h']) || !isset($_POST['g_w']) ||
	!isset($_POST['l_h']) || !isset($_POST['l_w']) ||
	!isset($_POST['s_h']) || !isset($_POST['s_w'])
){ 
	// recover any possible post data to auto-fill in fields that have been already completed
	$x = array();
	$y = array();
	$d = array();
	$x[0] = (!isset($_POST['x0'])) ? '""' : $_POST['x0'];
	$y[0] = (!isset($_POST['y0'])) ? '""' : $_POST['y0'];
	$d[0] = (!isset($_POST['d0'])) ? '""' : $_POST['d0'];

	$x[1] = (!isset($_POST['x1'])) ? '""' : $_POST['x1'];
	$y[1] = (!isset($_POST['y1'])) ? '""' : $_POST['y1'];
	$d[1] = (!isset($_POST['d1'])) ? '""' : $_POST['d1'];

	$x[2] = (!isset($_POST['x2'])) ? '""' : $_POST['x2'];
	$y[2] = (!isset($_POST['y2'])) ? '""' : $_POST['y2'];
	$d[2] = (!isset($_POST['d2'])) ? '""' : $_POST['d2'];

	$g_h = (!isset($_POST['g_h'])) ? '"500"' : $_POST['g_h'];
	$g_w = (!isset($_POST['g_w'])) ? '"500"' : $_POST['g_w'];

	$l_h = (!isset($_POST['l_h'])) ? '"19"' : $_POST['l_h'];
	$l_w = (!isset($_POST['l_w'])) ? '"19"' : $_POST['l_w'];
	
	$s_h = (!isset($_POST['s_h'])) ? '"10"' : $_POST['s_h'];
	$s_w = (!isset($_POST['s_w'])) ? '"10"' : $_POST['s_w'];
	echo '
<HTML>
	<HEAD>
	<TITLE>Trilateration</TITLE>
	<div align="center"><h1>Trilateration</h1></div>
	</HEAD>
	<BODY>
	<div align="center">
	<FORM method="post" action="' . $_SERVER['PHP_SELF'] . '">
			<B>Please fill in all fields to continue!</B>
			<BR/>
			<TABLE style="margin: 1px; padding: 0px; border: 1px solid #000000;">
				<TR><TD><B>Data</B></TD></TR>
				<TR>
					<TD>x1 <input type="text" size="4" maxlength="8" name="x0" value = ' . $x[0] . '></TD>
					<TD>y1 <input type="text" size="4" maxlength="8" name="y0" value = ' . $y[0] . '></TD>
					<TD>d1 <input type="text" size="4" maxlength="8" name="d0" value = ' . $d[0] . '></TD>
				</TR>
					
				<TR>
					<TD>x2 <input type="text" size="4" maxlength="8" name="x1" value = ' . $x[1] . '></TD>
					<TD>y2 <input type="text" size="4" maxlength="8" name="y1" value = ' . $y[1] . '></TD>
					<TD>d2 <input type="text" size="4" maxlength="8" name="d1" value = ' . $d[1] . '></TD>
				</TR>

				<TR>
					<TD>x3 <input type="text" size="4" maxlength="8" name="x2" value = ' . $x[2] . '></TD>
					<TD>y3 <input type="text" size="4" maxlength="8" name="y2" value = ' . $y[2] . '></TD>
					<TD>d3 <input type="text" size="4" maxlength="8" name="d2" value = ' . $d[2] . '></TD>
				</TR>
				<TR><TD><B>Graph</B></TD></TR>
				<TR>
					<TD>Graph Height (Pixels) <input type="text" size="4" maxlength="8" name="g_h" value = ' . $g_h . '></TD>
					<TD>Graph Width (Pixels) <input type="text" size="4" maxlength="8" name="g_w" value = ' . $g_w . '></TD>
				</TR>
				<TR>
					<TD>Vertical Lines <input type="text" size="4" maxlength="8" name="l_h" value = ' . $l_h . '></TD>
					<TD>Horizontal Lines <input type="text" size="4" maxlength="8" name="l_w" value = ' . $l_w . '></TD>
				</TR>
				<TR>
					<TD>Vertical Range <input type="text" size="4" maxlength="8" name="s_h" value = ' . $s_h . '></TD>
					<TD>Horizontal Range <input type="text" size="4" maxlength="8" name="s_w" value = ' . $s_w . '></TD>
				</TR>
				<TR>
				<td><br></TD>
				</TR>
				<TR>
					<TD>
					<input type="submit" value="Calculate!">
					</TD>
				</TR>
				</TABLE>
		</FORM>
			</div>
	</BODY>	
</HTML>
';
exit(0);
}

$p = array();
// Look at the trilat_coordinate.php for class definitions.
$p[0] = new dfcCoordinate((float) $_POST['x0'], (float) $_POST['y0'], (float) $_POST['d0']);
$p[1] = new dfcCoordinate((float) $_POST['x1'], (float) $_POST['y1'], (float) $_POST['d1']);
$p[2] = new dfcCoordinate((float) $_POST['x2'], (float) $_POST['y2'], (float) $_POST['d2']);

// Main calculation function
$p[3] = trilat_calc($p);
$rotations = 0;
// Start formatting
echo '<HTML><HEAD><div align="center"><TITLE>Trilateration Results</TITLE><div align="center"><h1>Trilateration</h1></div></HEAD><TABLE>';

for($i = 0;  $i<5;  $i++){
	if(abs($p[0]->getDist($p[3]) - $p[0]->getDFC()) > 0.01 || abs($p[1]->getDist($p[3]) - $p[1]->getDFC()) > 0.01 || abs($p[2]->getDist($p[3]) - $p[2]->getDFC()) > 0.01){ // if stuff doesn't match up
		if($i == 4){  // after last time
			echo "
			<TR>
			<B>NOTE:  </B>The points and distances that have been inputted did not match up to a single point using the trilateration formula.  The formula approximated and provided a possibly inaccurate answer.
			</TR>
			";  // warning if not
			break;
		}
		unset($p[3]);
		trilat_rotate($p);  // rotate the values for the formula
		$p[3] = trilat_calc($p);  // recalculate
		$rotations++;
	}
	else
		break;
}

echo '<TR><TD valign="top">';
tril_table($p); // display a table of values
echo "<BR>Rotations Required:  " . $rotations . "</TD><BR>";
echo "</TD>";

$graph = array(
	"height" => (int) $_POST['g_h'],
	"width" => (int) $_POST['g_w'],
	"height_lines" => (int) $_POST['l_h'],
	"width_lines" => (int) $_POST['l_w'],
	"height_size" => (int) $_POST['s_h'],
	"width_size" => (int) $_POST['s_w'],

); // make the graph

echo '<TD width ="' . ($graph['width'] + 50) . '" height ="' . ($graph['height'] + 40) . '" valign="bottom"  align = "right">'; 

tril_dgraph($graph);  // draw it
tril_plot($p[0],$graph, "p1");
tril_plot($p[1],$graph, "p2");
tril_plot($p[2],$graph, "p3");
tril_plot($p[3],$graph, "s", "./rpoint.png"); // special red point for solution
tril_egraph($graph); // end graph

echo '</TD></TR>
	<TR>
	<TD><A HREF="javascript:javascript:history.go(-1)"><B>Back!</B></A></TD>
	</TR>
	</TABLE></div></HTML>';  // back button

exit(0);
?>
