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


echo <<<EOHTML
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="5cm" height="3cm" viewBox="0 0 5 3" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
	<desc>Example link01 - a link on an ellipse</desc>
	<rect x=".01" y=".01" width="4.98" height="2.98" fill="none" stroke="blue"  stroke-width=".03"/>
	<a xlink:href="http://www.w3.org">
		<ellipse cx="2.5" cy="1.5" rx="2" ry="1" fill="red" />
	</a>
</svg>
EOHTML;

?>