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


define('PAGE_TYPE_ADD', 1);
define('PAGE_TYPE_EDIT', 2);

if (isset($_GET['editid'])) {
	$pagetype = PAGE_TYPE_EDIT;
	$editid = intval($_GET['editid']);
	$title = 'Edit Example';
} else {
	$pagetype = PAGE_TYPE_ADD;
	$title = 'Add Example';
}

$errormsg_html = array();

if (isset($_POST['formposted'])) {

	if (!$_POST['name']) {
		$errormsg_html[] = "'Name' not valid.";
	}

	if ( ($_POST['email']) && (!lib::chkemailvalid($_POST['email'])) ) {
		$errormsg_html[] = "'Email' not valid.";
	}

	$telephone = '';
	if ($_POST['telephone']) {

		//Telephone number
		if (!preg_match("%[^0-9\-\(\)\+\ ]%", $_POST['telephone'])) {

			$telephone = $_POST['telephone'];
			$telephone = str_replace('-', ' ', $telephone);
			$telephone = preg_replace("%[ \t]+%", ' ', $telephone);

			if (strlen($telephone) > 20) {
				$errormsg_html[] = "Error: 'Tel No' should be 20 characters or less";
			}

		} else {
			$errormsg_html[] = "Error: 'Tel No' (should be in the format e.g. 123 4567 8901, should contain 0-9, ()+ only)";
		}

	}

	//If no errors
	if (count($errormsg_html) == 0) {

		//$db->transaction(dbmysql::TRANSACTION_START);

		$record = array(
			'name' => $_POST['name'],
			'address' => $_POST['address'],
			'telephone' => $telephone,
			'email' => $_POST['email'],
		);

		$record = appgeneral::filternonascii_array($record);

		$record['province_id'] = ($_POST['province_id']) ? $_POST['province_id'] : null;

		if ($pagetype == PAGE_TYPE_EDIT) {

			$record = array_merge($record, array('lastupdated' => $db->datetimenow()));
			$db->record_update($tbl['example'], $db->rec($record), $db->cond(array("id = {$editid}"), 'AND'));

		} else {

			$record = array_merge($record, array('created' => $db->datetimenow(), 'lastupdated' => $db->datetimenow()));
			$db->record_insert($tbl['example'], $db->rec($record));
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
		$result = $db->table_query($db->tbl($tbl['example']), $db->col(array('name', 'address')), $db->cond(array("id = {$editid}"), 'AND'), '', 0, 1);
		if (!($record = $db->record_fetch($result))) {
			throw new Exception("Specified edit id \"{$editid}\" not found");
		}

		$formdata = $user_record;

	} else {

		$formdata = array(
			'name' => '',
			'address' => '',
			//
		);

	}

}


$self_args = array();

if ($pagetype == PAGE_TYPE_EDIT) {
	$self_args = array('editid' => $editid);
}


$link_h = navpd::self($self_args);

$formdatah = lib::htmlentities_array($formdata);



//Retrieve provinces
$province_options = '';
$province_result = $db->table_query($db->tbl($tbl['province']), $db->col(array('id', 'name')), $db->cond(array(), 'AND'), $db->order(array(array('name', 'ASC'))));
while ($province_record = $db->record_fetch($province_result)) {
	$id = $province_record['id'];
	$province_options[$id] = $province_record['name'];
}

$province_options_html = lib::create_options($province_options, $formdata['province_id'], lib::CREATEOPT_PLEASESELECT);

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
					<th class="addedit"><label for="address">Address:</label></th>
					<td class="addedit"><textarea rows="2" name="address" id="address" cols="20" class="inputtxtarea">{$formdatah['address']}</textarea></td>
				</tr>
				<tr>
					<th class="addedit"><label for="province_id">Province:</label></th>
					<td class="addedit"><select size="1" name="province_id" id="province_id">{$province_options_html}</select></td>
				</tr>
				<tr>
					<th class="addedit"><label for="telephone" class="inputxttitle">Telephone No:</label></th>
					<td class="addedit"><input type="text" name="telephone" id="telephone" value="{$formdatah['telephone']}" class="inputtxt" maxlength="20" /></td>
				</tr>
				<tr>
					<th class="addedit"><label for="email">Email:</label></th>
					<td class="addedit"><input type="text" name="email" id="email" value="{$formdatah['email']}" class="inputtxt" maxlength="255" /></td>
				</tr>
			</table>
		</div>

{$nav_html}

	</form>

</div>

EOHTML;


$template = new template();
$template->settitle($title);
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>