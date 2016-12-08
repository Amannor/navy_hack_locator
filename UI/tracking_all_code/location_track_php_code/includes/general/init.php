<?php

//Autoload classes
function __autoload($class_name) {
	global $includes_path;

	$include_file = $includes_path . 'classes/' . $class_name . '.php';
	if (!file_exists($include_file)) {
		throw new Exception("Class \"{$class_name}\" (\"{$include_file}\") not found");
	}

	require_once $include_file;

}

//If have a hostname (ie called from web)
if (isset($_SERVER['HTTP_HOST'])) {

	$site_url_host = parse_url($cfg['site_url'], PHP_URL_HOST);

	//If url should have www. at the start
	if (preg_match("/^www\.(.*)$/", $site_url_host, $site_url_parts)) {

		//If www. is not at the start
		if (!preg_match("/^www\./", $_SERVER['HTTP_HOST'])) {

			//If url matches what it should be, just without www.
			if ($_SERVER['HTTP_HOST'] == $site_url_parts[1]) {

				//Redirect to what the url should be
				header('Location: ' . $cfg['site_url']);

			}
		}

	}

}

?>