<?php

include $includes_path . 'config.php';
include $includes_path . 'general/init.php';

//Set exception handler
exceptions::sethandler();

//Connect to database
$db = new dbmysql($cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database']);

//Authentication
$auth = new auth();
$auth->handle();
$authinfo = $auth->getauthinfo();
$auth->login_required();


$map_id = intval($_GET['map_id']);

if ( (isset($_GET['upto_id'])) && ($_GET['upto_id']) ) {
	$upto_id = intval($_GET['upto_id']);
} else {
	$upto_id = false;
}

$tags = array();
if ( (isset($_GET['tagid'])) && (is_array($_GET['tagid'])) ) {

	foreach ($_GET['tagid'] as $tag_id) {
		$tags[] = intval($tag_id);
	}

}

$ajaxdata_js = ajaxdata::handle($map_id, $tags, $upto_id);

echo $ajaxdata_js;

?>