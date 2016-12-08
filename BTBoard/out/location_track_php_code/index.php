<?php

//Set paths
$includes_path = 'includes/';
$includes_pages_path = $includes_path . 'pages/';
$publichtml_path = '';

//Remove magic quotes
if (get_magic_quotes_gpc()) {

	function stripslashes_deep($value) {
		$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
		return $value;
	}

	$_POST = array_map('stripslashes_deep', $_POST);
	$_GET = array_map('stripslashes_deep', $_GET);
	$_COOKIE = array_map('stripslashes_deep', $_COOKIE);
	$_REQUEST = array_map('stripslashes_deep', $_REQUEST);

}

//If a page was passed in, make it safe and use it
if (isset($_GET['p'])) {
	$current_page = preg_replace("%[^a-zA-Z0-9_]%", '', $_GET['p']);
} else {
	//Set the default home page
	$current_page = 'livemap';
}

$self = pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME);
$pageself = $self."?p={$current_page}";

//Check page exists, otherwise give 404
if (!file_exists($includes_pages_path.$current_page.'.php')) {
	$current_page = 'error_404';
}

include $includes_pages_path . $current_page . '.php';

?>