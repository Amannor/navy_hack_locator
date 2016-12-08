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

$page_title = '';
$metadesc = '';

$link_base_path = htmlentities(navfr::base_path());

$body_html = <<<EOHTML


EOHTML;

$headeraddin_html = <<<EOHTML
<script src="{$link_base_path}resources/home/home.js" type="text/javascript"></script>
EOHTML;

$template = new template();
$template->settitle($page_title);
$template->setmetadesc($metadesc);
$template->setheaderaddinhtml($headeraddin_html);
//$template->setmainnavsection('home');
$template->setbodyhtml($body_html);
$template->display();

?>