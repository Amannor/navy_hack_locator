<?php

include $includes_path . 'config.php';
include $includes_path . 'general/init.php';

//Set exception handler
exceptions::sethandler();

//Connect to database
$db = new dbmysql($cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database']);

if ($cfg['system_mode_demo']) {
	throw new Exception('Can not accept data in demo mode');
}

throw new Exception("disabled");

//http://server/code/locationtrack/?p=clientapi&password=track111

/*
//Test Code
$_GET['password'] = $cfg['client_api_password'];

$_POST = array(
	'tags' => array(
		array(
			'addr' => '03A382',
			'firstseen' => '2010-01-01 12:00:00',
			'readers' => array(
				array(
					'addr' => '03A387',
					'signal' => 56,
				),
				array(
					'addr' => '03A379',
					'signal' => 51,
				),
				array(
					'addr' => '03A492',
					'signal' => 32,
				),
				array(
					'addr' => '03A531',
					'signal' => 57,
				),
			),
		),
	),
);
*/

//If have a log path, then log post data
if ($cfg['client_post_api_log']) {
	file_put_contents($cfg['client_post_api_log'], print_r(array(date('r'), $_GET, $_POST), true), FILE_APPEND);
}

//Check password specified
if (!isset($_GET['password'])) {
	throw new Exception('Password not specified');
}

//Check password is valid
if ($cfg['client_api_password'] != $_GET['password']) {
	throw new Exception('Password incorrect');
}

//Check tags available
if (!isset($_POST['tags'])) {
	throw new Exception('Tags array not specified');
}

//Retrieve details on all readers ready for lookups later
$readers_alldata = array();
$reader_result = $db->table_query($db->tbl($tbl['reader']), $db->col(array('id', 'map_id', 'addr', 'xpos', 'ypos')), $db->cond(array(), 'AND'));
while ($reader_record = $db->record_fetch($reader_result)) {
	$readers_alldata[] = $reader_record;
}

//Retrieve details on all tags ready for lookups later
$tags_alldata = array();
$tag_result = $db->table_query($db->tbl($tbl['tag']), $db->col(array('id', 'addr')), $db->cond(array(), 'AND'));
while ($tag_record = $db->record_fetch($tag_result)) {
	$tags_alldata[] = $tag_record;
}

//Retrieve details on all maps ready for lookups later
$maps_alldata = array();
$map_result = $db->table_query($db->tbl($tbl['map']), $db->col(array('id', 'width_px', 'height_px')), $db->cond(array(), 'AND'));
while ($map_record = $db->record_fetch($map_result)) {
	$id = $map_record['id'];
	$maps_alldata[$id] = $map_record;
}

//Go through all tags
foreach ($_POST['tags'] as $tag) {

	//Check tag address is valid
	if (!appgeneral::node_addr_valid($tag['addr'])) {
		throw new Exception("Node address \"{$tag['addr']}\" is not valid");
	}

	//Resolve tag id
	$tag_id = false;
	foreach ($tags_alldata as $tag_resolve) {
		if ($tag_resolve['addr'] == $tag['addr']) {
			$tag_id = $tag_resolve['id'];
			break;
		}
	}

	//If this is a known tag
	if ($tag_id !== false) {

		//Synapse Manuel: Because this value represents – (negative) dBm, lower values represent stronger signals, and higher values represent weaker signals.

		//Sort by signal (closer, ie lower signals first)
		$readers_closer = $tag['readers'];
		usort($readers_closer, 'reader_sort');

		$top3readers = array();

		//Go through all readers where this tag was seen
		$readerno = 0;
		foreach ($readers_closer as $reader) {

			//Check reader address is valid
			if (!appgeneral::node_addr_valid($tag['addr'])) {
				throw new Exception("Node address \"{$tag['addr']}\" is not valid");
			}

			//Check signal is valid
			if (!preg_match('/^\d{1,3}$/', $reader['signal'])) {
				throw new Exception("Signal \"{$reader['signal']}\" not numeric");
			}

			if (!( ($reader['signal'] >= 0) && ($reader['signal'] <= 127) )) {
				throw new Exception("Signal \"{$reader['signal']}\" not valid");
			}

			//Resolve readerdata from reader address
			$readerdata = false;
			foreach ($readers_alldata as $reader_resolve) {
				if ($reader_resolve['addr'] == $reader['addr']) {
					$readerdata = $reader_resolve;
					break;
				}
			}

			//If this is a known reader
			if ($readerdata !== false) {

				//If first reader, or map id matches that of first reader
				if ( ($readerno == 0) || ($top3readers[0]['readerdata']['map_id'] == $readerdata['map_id']) ) {

					//Note: Checking for map id match such that the reader with the lowest signal (first reader) to get a ping from the tag will trigger that readers map to be used if there are e.g. multiple maps setup in close proximity

					$top3readers[] = array_merge($reader, array('readerdata' => $readerdata));

					if ($cfg['client_api_log']) {
						file_put_contents($cfg['client_api_log'], "{$tag['firstseen']},{$tag['addr']},{$reader['addr']},{$reader['signal']}" . PHP_EOL, FILE_APPEND);
					}

					$readerno++;

					//Use top 3 lowest signal readers only
					if ($readerno >= 3) {
						break;
					}

				}

			}

		}

		//If have 3 readers (enough to do triangulation)
		if (count($top3readers) == 3) {

			//Prepare arrays for trilateration
			$a = array('x' => $top3readers[0]['readerdata']['xpos'], 'y' => $top3readers[0]['readerdata']['ypos'], 'dist' => $top3readers[0]['signal']);
			$b = array('x' => $top3readers[1]['readerdata']['xpos'], 'y' => $top3readers[1]['readerdata']['ypos'], 'dist' => $top3readers[1]['signal']);
			$c = array('x' => $top3readers[2]['readerdata']['xpos'], 'y' => $top3readers[2]['readerdata']['ypos'], 'dist' => $top3readers[2]['signal']);

			//Work out position of tag from reader locations / distances to tag
			$result = appgeneral::trilateration($a, $b, $c);

			//Lookup last sazed position
			$position_result = $db->table_query($db->tbl($tbl['position']), $db->col(array('xpos', 'ypos')), $db->cond(array("tag_id = {$tag_id}", "map_id = {$top3readers[0]['readerdata']['map_id']}"), 'AND'), $db->order(array(array('id', 'DESC'))), 0, 1);
			if ($position_record = $db->record_fetch($position_result)) {

				//Fuzzy movement detection
				if (
						(
							($result['x'] >= ($position_record['xpos'] - $cfg['fuzzy_movement_detection'])) && ($result['x'] <= ($position_record['xpos'] + $cfg['fuzzy_movement_detection'])) //x axis
						)
					&&
						(
							($result['y'] >= ($position_record['ypos'] - $cfg['fuzzy_movement_detection'])) && ($result['y'] <= ($position_record['ypos'] + $cfg['fuzzy_movement_detection'])) //y axis
						)
				) {
					//If moved >$cfg['fuzzy_movement_detection'] in the x or y direction, save position
					$save_new_pos = false;
				} else {
					$save_new_pos = true;
				}

			} else {
				$save_new_pos = true;
			}

			//If moved
			if ($save_new_pos) {

				$map_id = $top3readers[0]['readerdata']['map_id'];

				//Check position is within map bounds
				if ( ($result['x'] >= 0) && ($result['x'] <= $maps_alldata[$map_id]['width_px']) && ($result['y'] >= 0) && ($result['y'] <= $maps_alldata[$map_id]['height_px']) ) {

					$record = array(
						'tag_id' => $tag_id,
						'xpos' => $result['x'],
						'ypos' => $result['y'],
						'map_id' => $map_id,
						'reported' => $db->datetimenow(),
					);
					$db->record_insert($tbl['position'], $db->rec($record));

					echo "ok";

				} else {
					echo "error, calculated position {$result['x']},{$result['y']} is not within map bounds {$maps_alldata[$map_id]['width_px']},{$maps_alldata[$map_id]['height_px']}";
				}

			} else {
				echo "ok, not moved";
			}

		}

	}

}


//Sort readers by lowest signal first
function reader_sort($a, $b) {

	if ($a['signal'] == $b['signal']) {
		return 1;
	}
	return ($a['signal'] < $b['signal']) ? -1 : 1;

}

?>