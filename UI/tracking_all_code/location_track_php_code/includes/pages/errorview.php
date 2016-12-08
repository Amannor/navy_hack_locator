<?php

include $includes_path . 'config.php';
include $includes_path . 'general/init.php';

//Set exception handler
exceptions::sethandler();

//Connect to database
$db = new dbmysql($cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database']);

/*
//Authentication
$auth = new auth();
$auth->handle();
$authinfo = $auth->getauthinfo();
$auth->login_required_admin();
*/

throw new Exception('Unauthorised');

//Show exceptions
exceptions::viewlogs();

?>