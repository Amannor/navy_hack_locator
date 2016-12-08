<?php

include $includes_path . 'config.php';
include $includes_path . 'general/init.php';

//Set exception handler
exceptions::sethandler();

//Connect to database
$db = new dbmysql($cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database']);

if ($cfg['system_mode_demo']) {
	throw new Exception('Can not run simulation in demo mode');
}

header('Content-type: text/plain');

set_time_limit(60);



//Clear all existing position data from database
//$db->table_empty($tbl['position']);


/*
03A492
03A531
03A387
03A379
*/

/*

//Live data for trilateration

//240cm by 170cm room

$testdata_raw = <<<EODATA
#Tag location on map: Center
2010-01-13 19:31:05,03A382,03A492,33
2010-01-13 19:31:05,03A382,03A387,36
2010-01-13 19:31:05,03A382,03A531,43

#Tag location on map: Top Left
2010-01-13 19:31:15,03A382,03A387,24
2010-01-13 19:31:15,03A382,03A531,39
2010-01-13 19:31:15,03A382,03A379,49

#Tag location on map: Between Top Left, and Top Right
2010-01-13 19:31:26,03A382,03A379,34
2010-01-13 19:31:26,03A382,03A387,34
2010-01-13 19:31:26,03A382,03A492,44

#Tag location on map: Top Right
2010-01-13 19:31:37,03A382,03A379,26
2010-01-13 19:31:37,03A382,03A492,42
2010-01-13 19:31:37,03A382,03A387,52

#Tag location on map: Between Top Right, and Bottom Right
2010-01-13 19:31:47,03A382,03A492,37
2010-01-13 19:31:47,03A382,03A379,39
2010-01-13 19:31:47,03A382,03A531,46

#Tag location on map: Bottom Right
2010-01-13 19:31:58,03A382,03A492,20
2010-01-13 19:31:58,03A382,03A379,41
2010-01-13 19:31:58,03A382,03A387,49

#Tag location on map: Between Bottom Right, and Bottom Left
2010-01-13 19:32:09,03A382,03A531,40
2010-01-13 19:32:09,03A382,03A387,48
2010-01-13 19:32:09,03A382,03A379,49

#Tag location on map: Bottom Left
2010-01-13 19:32:19,03A382,03A531,19
2010-01-13 19:32:19,03A382,03A379,50
2010-01-13 19:32:19,03A382,03A492,54

#Tag location on map: Between Bottom Left, and Top Left
2010-01-13 19:32:30,03A382,03A387,33
2010-01-13 19:32:30,03A382,03A531,35
2010-01-13 19:32:30,03A382,03A492,40

#Tag location on map: Top Left
2010-01-13 19:32:41,03A382,03A387,19
2010-01-13 19:32:41,03A382,03A531,39
2010-01-13 19:32:41,03A382,03A492,45

#Tag location on map: Center
2010-01-13 19:32:51,03A382,03A379,39
2010-01-13 19:32:51,03A382,03A492,42
2010-01-13 19:32:51,03A382,03A387,43

EODATA;
*/

/*

//Live data for trilateration
//240cm by 170cm room

$testdata_raw = <<<EODATA
#Centre
2010-01-29 18:26:44,03A382,03A387,42
2010-01-29 18:26:44,03A382,03A492,42
2010-01-29 18:26:44,03A382,03A379,46

#Top Left
2010-01-29 18:28:19,03A382,03A387,19
2010-01-29 18:28:19,03A382,03A531,40
2010-01-29 18:28:19,03A382,03A492,45

#Between Top Left, Top Right
2010-01-29 18:29:57,03A382,03A531,47
2010-01-29 18:29:57,03A382,03A492,49
2010-01-29 18:29:57,03A382,03A379,50

#Top Right
2010-01-29 18:30:30,03A382,03A379,18
2010-01-29 18:30:30,03A382,03A492,35
2010-01-29 18:30:30,03A382,03A531,48

#Between Top Right, Bottom Right
2010-01-29 18:31:49,03A382,03A492,33
2010-01-29 18:31:49,03A382,03A379,38
2010-01-29 18:31:49,03A382,03A531,41

#Bottom Right
2010-01-29 18:32:21,03A382,03A492,19
2010-01-29 18:32:21,03A382,03A531,41
2010-01-29 18:32:21,03A382,03A387,49

#Between Bottom Right, Bottom Left
2010-01-29 18:33:07,03A382,03A492,36
2010-01-29 18:33:07,03A382,03A531,43
2010-01-29 18:33:07,03A382,03A387,52

#Bottom Left
2010-01-29 18:33:23,03A382,03A531,20
2010-01-29 18:33:23,03A382,03A492,39
2010-01-29 18:33:23,03A382,03A387,40

#Between Bottom Left, Top Left
2010-01-29 18:34:09,03A382,03A531,39
2010-01-29 18:34:09,03A382,03A387,40
2010-01-29 18:34:09,03A382,03A492,41

#Centre
2010-01-29 18:35:25,03A382,03A531,45
2010-01-29 18:35:25,03A382,03A387,47
2010-01-29 18:35:25,03A382,03A492,47
EODATA;
*/

//Simulation test data

$testdata_raw = <<<EODATA

#Red init
2010-01-29 12:00:00,100000,000001,1

#Green init
2010-01-29 12:00:00,200000,000001,1

#Blur init
2010-01-29 12:00:00,300000,000001,1

#Purple init
2010-01-29 12:00:00,400000,000001,1

#Orange init
2010-01-29 12:00:00,500000,000001,1

#Red to "Shop Area 2"
2010-01-29 12:00:02,100000,000005,1

#Red to "Shop Area 1"
2010-01-29 12:00:05,100000,000006,1

#Purple to "Shop Area 2"
2010-01-29 12:00:06,400000,000005,1

#Green to "Shop Area 2"
2010-01-29 12:00:06,200000,000005,1

#Blue to "Shop Area 2"
2010-01-29 12:00:05,300000,000005,1

#Red to "Reception"
2010-01-29 12:00:07,100000,000008,1

#Blue to "Shop Area 1"
2010-01-29 12:00:08,300000,000006,1

#Purple to "Shop Area 1"
2010-01-29 12:00:08,400000,000006,1

#Blue to "Office 2"
2010-01-29 12:00:10,300000,000002,1

#Purple to "Office"
2010-01-29 12:00:11,400000,000003,1

#Red to "Shop Area 1"
2010-01-29 12:00:12,100000,000006,1

#Orange to "Shop Area 2"
2010-01-29 12:00:12,500000,000005,1

#Red to "Staff Lounge"
2010-01-29 12:00:14,100000,000004,1

#Green to "Shop Area 1"
2010-01-29 12:00:15,200000,000006,1

#Orange to "Shop Area 1"
2010-01-29 12:00:16,500000,000006,1

#Orange to "Kitchen"
2010-01-29 12:00:17,500000,000007,1

#Green to "Reception"
2010-01-29 12:00:18,200000,000008,1

#Return

#Blue to "Shop Area 1"
2010-01-29 12:00:22,300000,000006,1

#Blue to "Shop Area 2"
2010-01-29 12:00:23,300000,000005,1

#Blue to "Stock Room"
2010-01-29 12:00:24,300000,000001,1

#Purple to "Shop Area 1"
2010-01-29 12:00:25,400000,000006,1

#Red to "Shop Area 1"
2010-01-29 12:00:26,100000,000006,1

#Orange to "Shop Area 1"
2010-01-29 12:00:27,500000,000006,1

#Green to "Shop Area 1"
2010-01-29 12:00:28,200000,000006,1

#Purple to "Shop Area 1"
2010-01-29 12:00:29,400000,000005,1

#Red to "Shop Area 2"
2010-01-29 12:00:30,100000,000005,1

#Orange to "Shop Area 2"
2010-01-29 12:00:31,500000,000005,1

#Green to "Shop Area 2"
2010-01-29 12:00:31,200000,000005,1

#Purple to "Stock Room"
2010-01-29 12:00:32,400000,000001,1

#Red to "Stock Room"
2010-01-29 12:00:32,100000,000001,1

#Orange to "Stock Room"
2010-01-29 12:00:33,500000,000001,1

#Green to "Stock Room"
2010-01-29 12:00:34,200000,000001,1

EODATA;



$testdata = array();

#Go through each line
$testdata_raw = str_replace("\r", '', $testdata_raw);
$testdata_lines = explode("\n", $testdata_raw);
foreach ($testdata_lines as $line) {

	#If data on the line
	if (trim($line)) {

		#If not a comment
		if (!preg_match('/^#/', $line)) {

			list($datetime, $tag_addr, $reader_addr, $signal) = explode(',', $line);

			$id = $datetime.'_'.$tag_addr;

			if (!isset($testdata[$id])) {
				$testdata[$id] = array(
					'addr' => $tag_addr,
					'firstseen' => $datetime,
					'readers' => array(),
				);
			}

			$testdata[$id]['readers'][] = array(
				'addr' => $reader_addr,
				'signal' => $signal,
			);

			//$tag_addr = $tag_addr;

		}

	}

}

$last_ts = false;
foreach ($testdata as $testdata_item) {

	if ($last_ts !== false) {
		$curr_ts = strtotime($testdata_item['firstseen'] . ' UTC');
		$sleep = $curr_ts - $last_ts;
		echo "Sleeping for {$sleep} seconds ...\n";
		sleep($sleep);
	}

	echo "Sending...\n";

	print_r($testdata_item);

	$postdata = array(
		'tags' => array($testdata_item),
	);

	$postdata_raw = http_build_query($postdata);

	$httprequest = new httprequest();
	$httprequest->seturl($cfg['site_url'] . '?p=clientapi&password=' . $cfg['client_api_password']);
	$httprequest->setuseragent('Location Track API Tester');
	$httprequest->setpostdata($postdata_raw);
	$httprequest->send();
	if ($httprequest->requestsuccess()) {
		echo 'Result: ' . $httprequest->gethttpdata() . "\n";
	} else {
		$errormsg = $httprequest->geterrormsg();
		throw new Exception('Curl reported error: ' . $errormsg);
	}

	echo "-----------------------------------\n";

	$last_ts = strtotime($testdata_item['firstseen'] . ' UTC');

	flush();

}

?>