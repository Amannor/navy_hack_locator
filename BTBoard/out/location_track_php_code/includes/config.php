<?php

//error_reporting(0);
error_reporting(E_ALL | E_STRICT);

//local
$cfg['db_server'] = 'localhost';
$cfg['db_username'] = 'dbuser';
$cfg['db_password'] = 'dbpass';
$cfg['db_database'] = 'dbname';
$cfg['db_prefix'] = 'lt_';

$cfg['devmode'] = true;

$cfg['system_mode_demo'] = false;

$cfg['site_name'] = 'Location Track';
$cfg['exceptions_logpath'] = $includes_path . 'other/exceptions.txt';

$cfg['site_url'] = 'http://www.example.com/locationtrack/';
$cfg['email_system_from'] = 'system@examplesite.com';

$cfg['client_api_password'] = 'track111';
$cfg['client_api_log'] = $includes_path . 'other/client_api_log.txt';
$cfg['client_post_api_log'] = $includes_path . 'other/client_post_api_log.txt';

$cfg['fuzzy_movement_detection'] = 10;

$cfg['livemap_ajax_refresh_sec'] = 1;

$cfg['email_method'] = 'smtp';

$tbl['user'] = $cfg['db_prefix'] . 'user';
$tbl['position'] = $cfg['db_prefix'] . 'position';
$tbl['tag'] = $cfg['db_prefix'] . 'tag';
$tbl['reader'] = $cfg['db_prefix'] . 'reader';
$tbl['map'] = $cfg['db_prefix'] . 'map';

$cfg['auth_cookie_expiry'] = 60 * 60 * 24 * 365;
$cfg['output_compressions'] = true;

$cfg['btn_template_path'] = $includes_path . 'other/btn_template/';
$cfg['btn_cache_path'] = $publichtml_path . 'resources/btn_cache/';
$cfg['btn_font_path'] = $cfg['btn_template_path'] . 'tahomabd.ttf';

date_default_timezone_set('Europe/London');
//date_default_timezone_set('America/Bahia');

$cfg['demo_username'] = 'demo@ns-tech.co.uk';

$cfg['map_file_maxsize'] = 2097152; //2MB

$cfg['maps_dir_path'] = 'resources/maps/';

$cfg['map_width_min'] = 50;
$cfg['map_width_max'] = 800;

$cfg['map_height_min'] = 50;
$cfg['map_height_max'] = 800;

$cfg['livemap_load_pos'] = 3;

$cfg['autofill_colours'] = array(
	'#f06363',
	'#88c54c',
	'#55aae0',
	'#c560f4',
	'#ffb85c',
);

?>