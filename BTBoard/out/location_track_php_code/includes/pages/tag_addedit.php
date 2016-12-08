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
	$title = 'Edit Tag';
} else {
	$pagetype = PAGE_TYPE_ADD;
	$title = 'Add Tag';
}

$errormsg_html = array();

if (isset($_POST['formposted'])) {

	if (!$_POST['name']) {
		$errormsg_html[] = "'Name' not specified.";
	}

	$addr = strtoupper($_POST['addr']);
	if (!appgeneral::node_addr_valid($addr)) {
		$errormsg_html[] = "'Node Address' not valid.";
	}

	//Check not a duplicate name / navname
	$cond_base = array();
	if ($pagetype == PAGE_TYPE_EDIT) {
		$cond_base[] = "id != {$editid}";
	}

	//Check for duplicate name
	$cond = array_merge($cond_base, array("name = '".$db->es($_POST['name'])."'"));
	$tag_result = $db->table_query($db->tbl($tbl['tag']), $db->col(array('id', 'name')), $db->cond($cond, 'AND'), '', 0, 1);
	if ($tag_record = $db->record_fetch($tag_result)) {
		$errormsg_html[] = "'Name' has already been used by Tag &quot;".htmlentities($reader_record['name'])."&quot;";
	}

	//Check for duplicate addr
	$cond = array_merge($cond_base, array("addr = '".$db->es($addr)."'"));
	$tag_result = $db->table_query($db->tbl($tbl['tag']), $db->col(array('id', 'name')), $db->cond($cond, 'AND'), '', 0, 1);
	if ($tag_record = $db->record_fetch($tag_result)) {
		$errormsg_html[] = "'Node Address' is already used by Tag &quot;".htmlentities($tag_record['name'])."&quot;";
	}

	$colour = strtolower($_POST['colour']);
	if (!appgeneral::colour_valid($colour)) {
		$errormsg_html[] = "'Colour' not valid.";
	}

	if ($cfg['system_mode_demo'] == true) {
		$errormsg_html[] = 'Feature not available in demo mode';
	}

	//If no errors
	if (count($errormsg_html) == 0) {

		//$db->transaction(dbmysql::TRANSACTION_START);

		$record = array(
			'name' => $_POST['name'],
			'addr' => $addr,
			'colour' => $colour,
		);

		$record = appgeneral::filternonascii_array($record);

		if ($pagetype == PAGE_TYPE_EDIT) {

			//$record = array_merge($record, array('lastupdated' => $db->datetimenow()));
			$db->record_update($tbl['tag'], $db->rec($record), $db->cond(array("id = {$editid}"), 'AND'));

		} else {

			//$record = array_merge($record, array('added' => $db->datetimenow(), 'lastupdated' => $db->datetimenow()));
			$db->record_insert($tbl['tag'], $db->rec($record));
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
		$tag_result = $db->table_query($db->tbl($tbl['tag']), $db->col(array('name', 'addr', 'colour')), $db->cond(array("id = {$editid}"), 'AND'), '', 0, 1);
		if (!($tag_record = $db->record_fetch($tag_result))) {
			throw new Exception("Specified edit id \"{$editid}\" not found");
		}

		$formdata = $tag_record;

	} else {

		$formdata = array(
			'name' => '',
			'addr' => '',
			'colour' => '',
		);

	}

}

$colour_options_html = '';
foreach ($cfg['autofill_colours'] as $colour) {
	$colour_options_html .= <<<EOHTML
<option value="{$colour}" style="color: {$colour}">{$colour}</option>
EOHTML;
}

$self_args = array();

if ($pagetype == PAGE_TYPE_EDIT) {
	$self_args = array('editid' => $editid);
}


$link_h = navpd::self($self_args);

$formdatah = lib::htmlentities_array($formdata);

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
					<th class="addedit"><label for="name">Name: <span class="required">*</span></label></th>
					<td class="addedit"><input type="text" name="name" id="name" value="{$formdatah['name']}" class="inputtxt" maxlength="255" /></td>
				</tr>
				<tr>
					<th class="addedit"><label for="addr">Node Address: <span class="required">*</span></label></th>
					<td class="addedit"><input type="text" name="addr" id="addr" value="{$formdatah['addr']}" class="inputtxt" maxlength="6" /></td>
				</tr>
				<tr>
					<th class="addedit"><label for="colour">Colour: <span class="required">*</span></label></th>
					<td class="addedit">
						<input type="text" name="colour" id="colour" value="{$formdatah['colour']}" class="inputtxt" maxlength="7" />
						<select name="colour_autofill" onchange="$('colour').value = this.value">
							<option value="">-- Autofill Colour --</option>
							{$colour_options_html}
						</select>
					</td>
				</tr>
			</table>
		</div>

{$nav_html}

	</form>

</div>

EOHTML;


$template = new template();
$template->setmainnavsection('tag');
$template->settitle('Tag > Add/Edit');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>