<?php

class appgeneral {

	static public function current_path() {
		global $cfg;

		$path = $_SERVER['PHP_SELF'];
		$path = ltrim($path, '/');
		$path_parts = explode('/', $path);

		$lastindex = count($path_parts) - 1;
		unset($path_parts[$lastindex]);

		$url_path = parse_url($cfg['site_url'], PHP_URL_PATH);
		$url_path = trim($url_path, '/');

		if ($url_path) {

			$path_parts_count = count(explode('/', $url_path));

			for ($i=1; $i<=$path_parts_count; $i++) {
				array_shift($path_parts);
			}

		}

		$current_path = ($path_parts) ? implode('/', $path_parts) . '/' : '/';

		return $current_path;

	}

	static public function trim_length($string, $trimlen) {
		if (strlen($string) > $trimlen) {
			 return substr($string, 0, $trimlen).'...';
		} else {
			return $string;
		}
	}

	static public function filternonascii_array($array) {
		$newarray = array();
		foreach ($array as $array_name => $array_data) {
			if (is_array($array_data)) {
				$newarray[$array_name] = self::filternonascii_array($array_data);
			} else {
				$newarray[$array_name] = self::filternonascii($array_data);
			}
		}
		return $newarray;
	}

	static public function filternonascii($string) {
		$newstring = preg_replace("%[^\040-\176\r\n\t]%", '', $string);
		return $newstring;
	}

	//Show 404
	static function show_404() {
		global $cfg;

		$body_html = <<<EOHTML
<h1>Error 404</h1>

<em>Page not found.</em>

<br />
<br />

<script type="text/javascript">
  var GOOG_FIXURL_LANG = 'en-GB';
  var GOOG_FIXURL_SITE = '{$cfg['site_url']}'
</script>
<script type="text/javascript"
  src="http://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js">
</script>

EOHTML;

		header('HTTP/1.0 404 Not Found');
		//header('Status: 404 Not Found');

		$template = new template();
		//$template->settitle('');
		$template->setbodyhtml($body_html);
		$template->display();

	}

	static public function errormsgs_html($errormsg_html) {

		//If errors, show them
		$errormsg_all_html = '';
		if (count($errormsg_html)) {
			foreach ($errormsg_html as $errormsg_item_html) {
				$errormsg_all_html .= <<<EOHTML
<div class="errormsg">Error: {$errormsg_item_html}</div>
EOHTML;
		}

		$errormsg_all_html = <<<EOHTML
<div class="errorcontainer">
{$errormsg_all_html}
</div>
EOHTML;

		} else {
			$errormsg_all_html = '';
		}

		return $errormsg_all_html;

	}

	static public function email_error($subject, $message) {
		global $cfg;

		//$link_error = navpd::link_fq(array('p' => 'errorview'));

		$message = <<<EOHTML
{$cfg['site_name']}
{$cfg['site_url']}

------------------------------

{$message}
EOHTML;

		$message = wordwrap($message, 70);

		$headers = 'From: ' . $cfg['email_system_from'];
		mail($cfg['email_admin'], $cfg['site_name'] . ' Error - ' . $subject, $message, $headers);

	}

	//Convert options to case statement for query
	static public function options_array_sql($options, $fieldname, $fieldname_resolved='') {
		global $db;

		if (!$fieldname_resolved) {
			$fieldname_resolved = $fieldname;
		}

		$options_sql = '';
		foreach ($options as $id => $value) {
			$options_sql .= "WHEN {$fieldname} = {$id} THEN '".$db->es($value)."' ";
		}

		$options_sql = "(SELECT CASE {$options_sql} END) AS {$fieldname_resolved}";

		return $options_sql;

	}

	//Text to HTML paragraphs
	static function text_to_paragraphs_html($text) {

		$html = htmlentities($text);

		$html = trim($html);

		//Convert to paragraphs
		$html_parts = explode("\n\n", $html);
		$total = count($html_parts);
		$html = '';
		$i = 0;
		foreach ($html_parts as $part) {
			$i++;
			$part = nl2br($part);
			$class = ($i == $total) ? ' class="last"' : '';

			$html .= <<<EOHTML
<p{$class}>{$part}</p>\n
EOHTML;
		}

		return $html;

	}

	//Delete map
	static function map_delete($map_id) {
		global $includes_path, $db, $tbl, $cfg;

		$map_id = intval($map_id);

		$map_path = $cfg['maps_dir_path'] . $map_id . '.png';

		$db->transaction(dbmysql::TRANSACTION_START);

		try {

			$db->record_delete($tbl['map'], $db->cond(array("id = " . intval($map_id)), 'AND'));

			$status = unlink($map_path);
			if (!$status) {
				throw new Exception("Unable to delete map file \"{$map_path}\"");
			}

			$db->transaction(dbmysql::TRANSACTION_COMMIT);

		} catch (Exception $e) {

			$db->transaction(dbmysql::TRANSACTION_ROLLBACK);

			throw $e;

		}

	}

	//Convert php array to js array
	static public function array_jsarray($array, $level=0) {

		if ($level > 10) {
			throw new Exception('Too much recursion');
		}

		$array_js = '';
		foreach ($array as $name => $value) {

			$name_s = addslashes($name);

			if (is_numeric($name)) {
				$n_q = '';
			} else {
				$n_q = '"';
			}

			if (is_array($value)) {

				$array_js_recur = self::array_jsarray($value, $level+1);
				$tabs = str_repeat("\t", $level+1);

				$array_js .= <<<EOJS
{$tabs}{$n_q}{$name_s}{$n_q}: {
{$array_js_recur}
{$tabs}},\n
EOJS;

			} else {

				$tabs = str_repeat("\t", $level+1);

				if (is_numeric($value)) {
					$q = '';
					$value_s = $value;
				} else if (is_bool($value)) {
					$q = '';
					$value_s = ($value) ? 'true' : 'false';
				} else if (is_null($value)) {
					$q = '';
					$value_s = null;
				} else {
					$q = '"';
					$value_s = addslashes($value);
					$value_s = str_replace("\r", '', $value_s);
					$value_s = str_replace("\n", '\n', $value_s);
					$value_s = str_replace("\t", '\t', $value_s);
				}

				$array_js .= <<<EOJS
{$tabs}{$n_q}{$name_s}{$n_q}: {$q}{$value_s}{$q},\n
EOJS;

			}

		}

		$array_js = rtrim($array_js, ",\n");

		if ($level == 0) {

			$array_js = <<<EOJS
{
{$array_js}
}
EOJS;

		}

		return $array_js;

	}

	//Check node address is valid
	static public function node_addr_valid($addr) {

		if (preg_match('/^[A-F0-9]{6}$/', $addr)) {
			return true;
		} else {
			return false;
		}

	}

	//Check colour is valid
	static public function colour_valid($colour) {

		if (preg_match('/^\#[a-f0-9]{6}$/', $colour)) {
			return true;
		} else {
			return false;
		}

	}

	//Trilatertion
	static public function trilateration($a, $b, $c) {
		// BOOST SIGNALS (distances) TO ENSURE OVERLAPPING CIRCLES
		// Do these circles overlap?
		// 1. determine distance between points
		// 2. determine whether combined radii is >= distance
		$ab = sqrt(pow($b['x'] - $a['x'], 2) + pow($b['y'] - $a['y'],2));
		$abr = $a['dist'] + $b['dist'];
		
		$bc = sqrt(pow($c['x'] - $b['x'], 2) + pow($c['y'] - $b['y'],2));
		$bcr = $b['dist'] + $c['dist'];
		
		$ca = sqrt(pow($a['x'] - $c['x'], 2) + pow($a['y'] - $c['y'],2));
		$car = $c['dist'] + $a['dist'];
		
		// If the distance between the centerpoints of any 2 circles is
		// greater than the combined signals, find amount needed to increase
		// signals to cause overlap of circles
		$abd = $bcd = $cad = 0;
		if ($abr < $ab) {
			$abd = ceil($ab - $abr);
		}
		
		if ($bcr < $bc) {
			$bcd = ceil($bc - $bcr);
		}
		
		if ($car < $ca) {
			$cad = ceil($ca - $car);
		}
		
		if ($abd > 0 || $bcd > 0 || $cad > 0) {
			// Determine greatest ratio between max required and actual
			$aratio = $ab / $abr;
			$bratio = $bc / $bcr;
			$cratio = $ca / $car;
				
			$modifier = max($aratio,$bratio,$cratio);
			
			$a['dist'] = $a['dist'] * $modifier;
			$b['dist'] = $b['dist'] * $modifier;
			$c['dist'] = $c['dist'] * $modifier;
		}
	
		// ROTATION //////////////////////////////////////
		// 	The process of trilateration finds the location of a point that is described in terms of fixed 
		// 	distances to three known points. In order for this process to be executed with minimal computation, 
		// 	the three known points must be oriented such that one point represents the origin (0, 0) 
		// 	and one point represents a point on the x-axis (p, 0). The third point (q, r) will be realigned
		// 	according to its relative position to the first two.
		// Given a, b, c, return new coordinates with a at 0,0, b at x,0 and resultant c
		$ax0 = $ax = $a['x'];
		$ay0 = $ay = $a['y'];
		
		$bx = $b['x'];
		$by = $b['y'];
	
		$cx0 = $cx = $c['x'];
		$cy0 = $cy = $c['y'];	
		
		// Move a to 0,0 by simple x/y translation
		$xmove = $ax;
		$ymove = $ay;
		
		$ax = 0;
		$ay = 0;
		
		$bx = $bx - $xmove;
		$by = $by - $ymove;
		
		$cx = $cx - $xmove;
		$cy = $cy - $ymove;
		
		// Now, move b to x, 0 by rotation
		
		// Theta: 1.030376827 =ATAN2(B9, C9)
		// Phi: 0 =ATAN2(B10, C10)
		// R1: 17.49285568 =SQRT((B4-B3)^2+(C4-C3)^2)
		// R2: 21 =SQRT((B5-B3)^2+(C5-C3)^2)
		$theta = atan2($by,$bx);
		$phi = atan2($cy,$cx);
		$r1 = sqrt( pow($bx - $ax,2) + pow($by - $ay,2) );
		$r2 = sqrt( pow($cx - $ax,2) + pow($cy - $ay,2) );
		
		// Leaving polar coordinates for rotated system:
		$ar = 0;
		$at = 0;
		
		$br = $r1;
		$bt = 0;
		
		$cr = $r2;
		$ct = $phi - $theta;
		
		// Convert to rectangular coords
		// ax/ay already determined.
		// by already determined. bx is already calculated
		$bx = $r1;
		
		// Must calculate both for c
		$cx = $cr * cos($ct);
		$cy = $cr * sin($ct);
		// END ROTATION //////////////////////////////////////
		
		// Now that we have translated coords, calculate rotated solution.
		$ra = $a['dist'];
		$rb = $b['dist'];
		$rc = $c['dist'];	
		
		$dx = ( pow($ra,2) - pow($rb,2) + pow($bx, 2) )/($bx * 2);
	
		$dy1 = sqrt( pow($ra,2) - pow($dx,2) ); // value is +/-
		$dy2 = 0 - $dy1;
		
		// Convert solution to polar coordinates
		$dr = sqrt( pow($dx,2) + pow($dy1, 2) ); // SQRT(B31^2+B32^2)
		$dt1 = atan2($dy1,$dx); // ATAN2(B31,B32) value is +/-
		$dt2 = 0 - $dt1;
		
		// Unrotate solution based on 2 possible solutions.
		$dt1 = $dt1 + $theta;
		$dt2 = $dt2 + $theta;
		
		// Convert solution to rectangular coords
		$dx1 = $dr * cos($dt1);
		$dy1 = $dr * sin($dt1);
		
		$dx2 = $dr * cos($dt2);
		$dy2 = $dr * sin($dt2);
		
		// Untranslate from translation of point 1 to 0,0
		$dx1 += $ax0;
		$dy1 += $ay0;
		
		$dx2 += $ax0;
		$dy2 += $ay0;
		
		// Test solutions...
		// Calculated distance to C, solution 1
		$cd1 = sqrt( pow($dx1 - $cx0,2) + pow($dy1 - $cy0,2) );
		$cdiff1 = abs($rc - $cd1);
			
		// Calculated distance to C, solution 2
		$cd2 = sqrt( pow($dx2 - $cx0,2) + pow($dy2 - $cy0,2) );
		$cdiff2 = abs($rc - $cd2);
	
		// Use the smallest difference
		if ($cdiff1 < $cdiff2) {
			$dx = round($dx1);
			$dy = round($dy1);
		} else {
			$dx = round($dx2);
			$dy = round($dy2);
		}
		
		return array('x' => $dx, 'y' => $dy);
	}
}
	
	
// 	static public function trilateration($a, $b, $c) {
// 		global $cfg;	
// 		if ($cfg['client_post_api_log']) {
// 			file_put_contents($cfg['client_post_api_log'], print_r(array($a, $b, $c), true), FILE_APPEND);
// 		}
// 		
// 		// x, y, dist
// 
// 		//http://london.mnetcs.com/Trilateration/
// 		//http://milesburton.com/wiki/index.php?title=2D_Positioning_-_Trilateration_in_JavaScript
// /*
// 		$s = (pow($c['x'], 2) - pow($b['x'], 2) + pow($c['y'], 2) - pow($b['y'], 2) + pow($b['dist'], 2) - pow($c['dist'], 2) ) / 2;
// 		$t = (pow($a['x'], 2) - pow($b['x'], 2) + pow($a['y'], 2) - pow($b['y'], 2) + pow($b['dist'], 2) - pow($a['dist'], 2) ) / 2;
// 
// 		$unknown_y = ( ( $t * ( $b['x'] - $c['x'] ) ) - ( $s * ( $b['x'] - $a['x'] ) ) ) / ( ( ( $a['y'] - $b['y'] ) * ( $b['x'] - $c['x'] ) ) - ( ( $c['y'] - $b['y'] ) * ( $b['x'] - $a['x'] ) ) );
// 		$unknown_x = ( ( $unknown_y * ( $a['y'] - $b['y'] ) ) - $t ) / ( $b['x'] - $a['x'] );
// */
// 
// //		$unknown_y = 100;
// //		$unknown_x = 100;
// 
// 
// 		$xa = $a['x'];
// 		$ya = $a['y'];
// 		$ra = $a['dist'];
// 		
// 		$xb = $b['x'];
// 		$yb = $b['y'];
// 		$rb = $b['dist'];
// 		
// 		$xc = $c['x'];
// 		$yc = $c['y'];
// 		$rc = $c['dist'];
// 		
// 	// 	The process of trilateration finds the location of a point that is described in terms of fixed 
// 	// 	distances to three known points. In order for this process to be executed with minimal computation, 
// 	// 	the three known points must be oriented such that one point represents the origin (0, 0) 
// 	// 	and one point represents a point on the x-axis (p, 0). The third point (q, r) will be realigned
// 	// 	according to its relative position to the first two.
// 		// Move a to 0,0
// 		// remember original location
// 		$xa0 = $xa;
// 		$ya0 = $ya;
// 		
// 		$xa = 0;
// 		$ya = 0;
// 		
// 		$ra0 = 0; // no distance at 0,0
// 		
// 		// Update other points
// 		$xb -= $xa0;
// 		$yb -= $ya0;
// 		
// 		$xc -= $xa0;
// 		$yc -= $ya0;
// 		
// 		// Move b to x-axis: (p,0)
// 		// This requires rotation of the triangle about the 0,0 axis. We must convert to polar coordinates
// 		$rb0 = sqrt(abs($xb^2 + $yb^2));
// 	
// 		// Rotation needed to get to 0;
// 		if ($xb == 0) {
// 			$theta0 = pi()/2;
// 		} else {
// 			$theta0 = atan2($yb,$xb);
// 		}
// 		
// 		// The current distance IS the new xb
// 		$yb = 0;
// 		$xb = $rb0;
// 		
// 	//echo "xb: $xb \n";
// 		
// 		// Rotate point 3 at $theta0 radians to new x/y
// 		// Get current r/theta for point 3
// 		$rc0 = sqrt($xc^2 + $yc^2);
// 		$theta1 = atan2($yc,$xc);
// 	
// 		// Perform transform of point 3 to theta == theta0
// 		// Get new theta by adding/subtracting previous theta
// 		$theta2 = $theta0 + $theta1;
// 		
// 		$xc = abs($rc0 * cos($theta2));
// 		$yc = $rc0 * sin($theta2);
// 	
// 		$x = round(abs( ($ra0^2 - $rb0^2 + $xb^2) / (2 * $xb) ));
// 		$y = round(sqrt(abs( ($xb^2 - ($ra0^2 - $rb0^2 + $xb^2)^2) / (2 * $xb))));
// 	
// 		if ($cfg['client_post_api_log']) {
// 			file_put_contents($cfg['client_post_api_log'], print_r(array($x, $y), true), FILE_APPEND);
// 		}
// 	
// 		return array('x' => $x, 'y' => $y);
// 	}
// }

?>