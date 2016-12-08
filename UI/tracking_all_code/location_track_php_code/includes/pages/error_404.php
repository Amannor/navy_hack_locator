<?php

include $includes_path . 'config.php';
include $includes_path . 'general/init.php';

//Set exception handler
exceptions::sethandler();

//Connect to database
$db = new dbmysql($cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database']);

$body_html = <<<EOHTML
<h1>Error 404</h1>

<em>Page not found.</em>
EOHTML;

header('HTTP/1.0 404 Not Found');
//header('Status: 404 Not Found');

$template = new template();
//$template->settitle('');
$template->setbodyhtml($body_html);
$template->display();

?>