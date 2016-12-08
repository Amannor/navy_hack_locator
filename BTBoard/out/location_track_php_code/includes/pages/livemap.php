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


//Retrieve all map options
$mapdata = array();
$map_options = array();
$map_result = $db->table_query($db->tbl($tbl['map']), $db->col(array('id', 'name', 'width_px', 'height_px')), $db->cond(array(), 'AND'), $db->order(array(array('name', 'ASC'))));
while ($map_record = $db->record_fetch($map_result)) {
	$id = $map_record['id'];
	$mapdata[$id] = $map_record;
	$map_options[$id] = $map_record['name'];
}

//If have a map specified
if ( (isset($_GET['map_id'])) && ($_GET['map_id']) ) {
	$map_id = intval($_GET['map_id']);
} else {

	//Default to first map
	foreach ($mapdata as $mapitem) {
		$map_id = $mapitem['id'];
		break;
	}

}

if (!$map_id) {

	$body_html = 'Add map first';

	$template = new template();
	$template->settitle($page_title);
	$template->setheaderaddinhtml($headeraddin_html);
	//$template->setmainnavsection('home');
	$template->usejquerysvg(true);
	$template->setbodyhtml($body_html);
	$template->display();

}

if (!isset($mapdata[$map_id])) {
	throw new Exception("Map id \"{$map_id}\" not valid");
}

$currmapdata = $mapdata[$map_id];

//Load in all reader location
$reader_locations = array();
$reader_result = $db->table_query($db->tbl($tbl['reader']), $db->col(array('id', 'name', 'xpos', 'ypos')), $db->cond(array("map_id = {$map_id}"), 'AND'), $db->order(array(array('name', 'ASC'))));
while ($reader_record = $db->record_fetch($reader_result)) {

	settype($reader_record['id'], 'integer');
	settype($reader_record['xpos'], 'integer');
	settype($reader_record['ypos'], 'integer');

	$id = $reader_record['id'];

	$reader_locations[$id] = $reader_record;

}


$reader_locations_js = appgeneral::array_jsarray($reader_locations);

$link_base_path = htmlentities(navfr::base_path());

$map_options_html = lib::create_options($map_options, $map_id);

$self_linkh = navpd::link_h();
$form_map_self = navpd::self_form(array('map_id' => null));

$map_background = $cfg['maps_dir_path'] . $map_id . '.png';
$map_background_js = addslashes($map_background);

$page_title = 'Live Map';

//Retrieve all tags
$tags = array();
$map_key_html = '';
$tag_result = $db->table_query($db->tbl($tbl['tag']), $db->col(array('id', 'addr', 'name', 'colour')), $db->cond(array(), 'AND'), $db->order(array(array('name', 'ASC'))));
while ($tag_record = $db->record_fetch($tag_result)) {

	$id = $tag_record['id'];
	$tags[$id] = $tag_record;

	$nameh = htmlentities($tag_record['name']);

	$map_key_html .= <<<EOHTML
<li><img src="{$link_base_path}resources/livemap/sitepanel/marker.png" style="background-color: {$tag_record['colour']}" width="14" height="14" /> {$nameh}</li>
EOHTML;

}

$tags_js = appgeneral::array_jsarray($tags);

$baseurl_js = addslashes(navpd::link(array('p' => 'livemap_ajax', 'map_id' => $map_id)));

$ajaxdata_js = ajaxdata::handle($map_id, array_keys($tags), false);

$body_html = <<<EOHTML

<div id="svgmapcontainer" class="svgmapcontainer" style="width: {$currmapdata['width_px']}px; height: {$currmapdata['height_px']}px;"></div>

<div class="panel_filter">

	<div class="panelitem">

		<form method="get" action="{$self_linkh}">
			<div>
				{$form_map_self}
			</div>

			<div class="row">
				<div class="title"><label for="map_id">Map</label></div>
				<div class="userinput"><select size="1" name="map_id" id="map_id" onchange="submit()" class="inputselect">{$map_options_html}</select></div>
			</div>
		</form>

	</div>

	<div class="panelitem panelitem-key">

		<div class="title">Tag Key</div>

		<ul>
{$map_key_html}
		</ul>

	</div>

</div>

<div class="clear"></div>

<em>Note: Compatible browser required, e.g. Firefox, Chrome, Safari.</em>

<script type="text/javascript">
  <!--

	var map_background = "{$map_background_js}";
	var map_background_width = "{$currmapdata['width_px']}";
	var map_background_height = "{$currmapdata['height_px']}";

	var reader_locations = {$reader_locations_js};

	var tags = {$tags_js};

	var baseurl = "{$baseurl_js}";

	var ajaxdata = {$ajaxdata_js};

	var livemap_ajax_refresh_sec = {$cfg['livemap_ajax_refresh_sec']}

  //-->
</script>

EOHTML;

$headeraddin_html = <<<EOHTML
<script src="{$link_base_path}resources/livemap/livemap.js" type="text/javascript"></script>
EOHTML;

$template = new template();
$template->settitle($page_title);
$template->setheaderaddinhtml($headeraddin_html);
//$template->setmainnavsection('home');
$template->usejquerysvg(true);
$template->setbodyhtml($body_html);
$template->display();

?>