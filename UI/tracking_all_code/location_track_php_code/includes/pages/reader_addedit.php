<?php

include $includes_path . 'config.php';
include $includes_path . 'general/init.php';

//Set exception handler
exceptions::sethandler();

//Connect to database
$db = new dbmysql($cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database']);

//Authentication
$admin_auth = new auth();
$admin_auth->handle();
$authinfo = $admin_auth->getauthinfo();
$admin_auth->login_required();


define('PAGE_TYPE_ADD', 1);
define('PAGE_TYPE_EDIT', 2);

if (isset($_GET['editid'])) {
	$pagetype = PAGE_TYPE_EDIT;
	$editid = intval($_GET['editid']);
	$title = 'Edit Reader';
} else {
	$pagetype = PAGE_TYPE_ADD;
	$title = 'Add Reader';
}

$errormsg_html = array();

if (isset($_POST['formposted'])) {

	if (!$_POST['name']) {
		$errormsg_html[] = "'Name' not specified.";
	}

	if (!appgeneral::node_addr_valid($_POST['addr'])) {
		$errormsg_html[] = "'Node Address' not valid.";
	}

	if (!$_POST['map_id']) {
		$errormsg_html[] = "'Map' not specified.";
	}

	if (strlen($_POST['xpos'])) {
		if (preg_match("%[^0-9]%", $_POST['xpos'])) {
			$errormsg_html[] = "'X Pos' must be numeric";
		}
	} else {
		$errormsg_html[] = "'X Pos' not specified.";
	}

	if (strlen($_POST['ypos'])) {
		if (preg_match("%[^0-9]%", $_POST['ypos'])) {
			$errormsg_html[] = "'Y Pos' must be numeric";
		}
	} else {
		$errormsg_html[] = "'Y Pos' not specified.";
	}

	//Check not a duplicate name / navname
	$cond_base = array();
	if ($pagetype == PAGE_TYPE_EDIT) {
		$cond_base[] = "id != {$editid}";
	}

	//Check for duplicate name
	$cond = array_merge($cond_base, array("name = '".$db->es($_POST['name'])."'", "map_id = '".$db->es($_POST['map_id'])."'"));
	$reader_result = $db->table_query($db->tbl($tbl['reader']), $db->col(array('id', 'name')), $db->cond($cond, 'AND'), '', 0, 1);
	if ($reader_record = $db->record_fetch($reader_result)) {
		$errormsg_html[] = "'Name' has already been used by Reader &quot;".htmlentities($reader_record['name'])."&quot;";
	}

	//Check for duplicate address
	$cond = array_merge($cond_base, array("addr = '".$db->es($_POST['addr'])."'", "map_id = '".$db->es($_POST['map_id'])."'"));
	$reader_result = $db->table_query($db->tbl($tbl['reader']), $db->col(array('id', 'name')), $db->cond($cond, 'AND'), '', 0, 1);
	if ($reader_record = $db->record_fetch($reader_result)) {
		$errormsg_html[] = "'Node Address' is already used by Reader &quot;".htmlentities($reader_record['name'])."&quot;";
	}

	if ($_POST['map_id']) {

		//Check for position outside map area
		$map_result = $db->table_query($db->tbl($tbl['map']), $db->col(array('width_px', 'height_px')), $db->cond(array("id = ".intval($_POST['map_id'])), 'AND'), '', 0, 1);
		if (!($map_record = $db->record_fetch($map_result))) {
			throw new Exception("Unable to retrieve details for map id \"{$editid}\"");
		}

		if ( ($_POST['xpos'] > $map_record['width_px']) || ($_POST['ypos'] > $map_record['height_px']) ) {
			$errormsg_html[] = htmlentities("Error: Reader position ({$_POST['xpos']}x{$_POST['ypos']}) is outside of viewable map area ({$map_record['width_px']}x{$map_record['height_px']}).");
		}

	}

	if ($cfg['system_mode_demo'] == true) {
		$errormsg_html[] = 'Feature not available in demo mode';
	}

	//If no errors
	if (count($errormsg_html) == 0) {

		//$db->transaction(dbmysql::TRANSACTION_START);

		$record = array(
			'name' => $_POST['name'],
			'addr' => $_POST['addr'],
			'map_id' => $_POST['map_id'],
			'xpos' => $_POST['xpos'],
			'ypos' => $_POST['ypos'],
		);

		$record = appgeneral::filternonascii_array($record);

		if ($pagetype == PAGE_TYPE_EDIT) {

			//$record = array_merge($record, array('lastupdated' => $db->datetimenow()));
			$db->record_update($tbl['reader'], $db->rec($record), $db->cond(array("id = {$editid}"), 'AND'));

		} else {

			//$record = array_merge($record, array('added' => $db->datetimenow(), 'lastupdated' => $db->datetimenow()));
			$db->record_insert($tbl['reader'], $db->rec($record));
			$editid = $db->record_insert_id();
			$pagetype = PAGE_TYPE_EDIT;

		}

		//$db->transaction(dbmysql::TRANSACTION_COMMIT);

		header("Location: {$cfg['site_url']}" . navpd::back());

	}

}




//If page posted
if (isset($_POST['formposted'])) {

	$formdata = $_POST;

} else {

	//If page type edit
	if ($pagetype == PAGE_TYPE_EDIT) {

		//Retrieve table data
		$reader_result = $db->table_query($db->tbl($tbl['reader']), $db->col(array('name', 'addr', 'map_id', 'xpos', 'ypos')), $db->cond(array("id = {$editid}"), 'AND'), '', 0, 1);
		if (!($reader_record = $db->record_fetch($reader_result))) {
			throw new Exception("Specified edit id \"{$editid}\" not found");
		}

		$formdata = $reader_record;

	} else {

		$formdata = array(
			'name' => '',
			'addr' => '',
			'map_id' => '',
			'xpos' => '',
			'ypos' => '',
		);

	}

}


$self_args = array();

if ($pagetype == PAGE_TYPE_EDIT) {
	$self_args = array('editid' => $editid);
}


$link_h = navpd::self($self_args);

$formdatah = lib::htmlentities_array($formdata);

//Retrieve maps
$map_options = array();
$map_result = $db->table_query($db->tbl($tbl['map']), $db->col(array('id', 'name')), $db->cond(array(), 'AND'), $db->order(array(array('name', 'ASC'))));
while ($map_record = $db->record_fetch($map_result)) {
	$id = $map_record['id'];
	$map_options[$id] = $map_record['name'];
}

$map_options_html = lib::create_options($map_options, $formdata['map_id'], lib::CREATEOPT_PLEASESELECT);


//Convert errors to html
$errormsgs_html = appgeneral::errormsgs_html($errormsg_html);

$btn_back = btn::create('<< Back', btn::TYPE_LINK, navpd::back(), '', '', 'Back');
$btn_update = btn::create('Update', btn::TYPE_SUBMIT);

$nav_html = <<<EOHTML
<div class="navigation">
	<div class="left">{$btn_back}</div>
	<div class="right">{$btn_update}</div>
</div>
EOHTML;

$body_html = <<<EOHTML

<div class="addeditpage">

	<h2>{$title}</h2>

	<form method="post" action="{$link_h}" id="mainform" class="mainform">
		<div><input type="hidden" name="formposted" value="1" /></div>

{$nav_html}

{$errormsgs_html}

		<div class="tablecontainer">
			<table cellspacing="0" class="addedit">
				<tr>
					<th class="addedit"><label for="map_id">Map: <span class="required">*</span></label></th>
					<td class="addedit"><select name="map_id" id="map_id">{$map_options_html}</select></td>
				</tr>
				<tr>
					<th class="addedit"><label for="name">Name: <span class="required">*</span></label></th>
					<td class="addedit"><input type="text" name="name" id="name" value="{$formdatah['name']}" class="inputtxt" maxlength="255" /></td>
				</tr>
				<tr>
					<th class="addedit"><label for="addr">Node Address: <span class="required">*</span></label></th>
					<td class="addedit"><input type="text" name="addr" id="addr" value="{$formdatah['addr']}" class="inputtxt" maxlength="6" /></td
				</tr>
				<tr>
					<th class="addedit"><label for="xpos">X Pos: <span class="required">*</span></label></th>
					<td class="addedit"><input type="text" name="xpos" id="xpos" value="{$formdatah['xpos']}" class="inputtxt" maxlength="4" /></td
				</tr>
				<tr>
					<th class="addedit"><label for="ypos">Y Pos: <span class="required">*</span></label></th>
					<td class="addedit"><input type="text" name="ypos" id="ypos" value="{$formdatah['ypos']}" class="inputtxt" maxlength="4" /></td
				</tr>
			</table>
		</div>

{$nav_html}

	</form>

</div>

EOHTML;


$template = new template();
$template->setmainnavsection('reader');
$template->settitle('Reader > Add/Edit');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>