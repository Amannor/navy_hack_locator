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
	$title = 'Edit User';
} else {
	$pagetype = PAGE_TYPE_ADD;
	$title = 'Add User';
}


$errormsg_html = array();

if (isset($_POST['formposted'])) {

	if ($_POST['email']) {
		if (!lib::chkemailvalid($_POST['email'])) {
			$errormsg_html[] = "'Email' not valid.";
		} else {

			//Check not a duplicate name / navname
			$cond_base = array();
			if ($pagetype == PAGE_TYPE_EDIT) {
				$cond_base[] = "id != {$editid}";
			}

			//Check for duplicate email
			$cond = array_merge($cond_base, array("email = '".$db->es($_POST['email'])."'"));
			$user_result = $db->table_query($db->tbl($tbl['user']), $db->col(array('id')), $db->cond($cond, 'AND'), '', 0, 1);
			if ($user_record = $db->record_fetch($user_result)) {
				$errormsg_html[] = "'Email' is already setup with an account";
			}

		}
	} else {
		$errormsg_html[] = "'Email' not specified.";
	}

	if ($pagetype == PAGE_TYPE_ADD) {
		if (!$_POST['password_new']) {
			$errormsg_html[] = "'Password' not specified.";
		} else {
			if ($_POST['password_new'] != $_POST['password_new_confirm']) {
				$errormsg_html[] = "'Password' does not match 'Password Confirm'.";
			}
		}
	} else {
		if ( ($_POST['password_new']) || ($_POST['password_new']) ) {
			if ($_POST['password_new'] != $_POST['password_new_confirm']) {
				$errormsg_html[] = "'Password' does not match 'Password Confirm'.";
			}
		}
	}

	if (!$_POST['name']) {
		$errormsg_html[] = "'Name' not specified.";
	}

	if ($cfg['system_mode_demo'] == true) {
		$errormsg_html[] = 'Feature not available in demo mode';
	}

	//If no errors
	if (count($errormsg_html) == 0) {

		try {

			$db->transaction(dbmysql::TRANSACTION_START);

			$record = array(
				'email' => $_POST['email'],
				'name' => $_POST['name'],
			);

			if ($_POST['password_new']) {
				$record = array_merge($record, array('password' => md5($_POST['password_new'])));
			}

			$record = appgeneral::filternonascii_array($record);

			if ($pagetype == PAGE_TYPE_EDIT) {

				//$record = array_merge($record, array('lastupdated' => $db->datetimenow()));
				$db->record_update($tbl['user'], $db->rec($record), $db->cond(array("id = {$editid}"), 'AND'));

			} else {

				$record = array_merge($record, array('registered' => $db->datetimenow()));
				$db->record_insert($tbl['user'], $db->rec($record));
				$editid = $db->record_insert_id();
				$pagetype = PAGE_TYPE_EDIT;

			}

			$db->transaction(dbmysql::TRANSACTION_COMMIT);

			header("Location: {$cfg['site_url']}" . navpd::back());

		} catch (Exception $exception) {

			$db->transaction(dbmysql::TRANSACTION_ROLLBACK);

			throw $exception;

		}

	}

}



//If page posted
if (isset($_POST['formposted'])) {

	$formdata = $_POST;

} else {

	//If page type edit
	if ($pagetype == PAGE_TYPE_EDIT) {

		//Retrieve table data
		$user_result = $db->table_query($db->tbl($tbl['user']), $db->col(array('email', 'name')), $db->cond(array("id = {$editid}"), 'AND'), '', 0, 1);
		if (!($user_record = $db->record_fetch($user_result))) {
			throw new Exception("Specified edit id \"{$editid}\" not found");
		}

		$formdata = $user_record;

	} else {

		$formdata = array(
			'email' => '',
			'name' => '',
		);

	}

}

$formdata['password_new'] = '';
$formdata['password_new_confirm'] = '';


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
					<th class="addedit"><label for="email">Email: <span class="required">*</span></label></th>
					<td class="addedit"><input type="text" name="email" id="email" value="{$formdatah['email']}" class="inputtxt" maxlength="255" /></td>
				</tr>
				<tr>
					<th class="addedit"><label for="password_new">Password: </label></th>
					<td class="addedit"><input type="password" name="password_new" id="password_new" value="{$formdatah['password_new']}" class="inputtxt" maxlength="255" /></td>
				</tr>
				<tr>
					<th class="addedit"><label for="password_new_confirm">Password Confirm: </label></th>
					<td class="addedit"><input type="password" name="password_new_confirm" id="password_new_confirm" value="{$formdatah['password_new_confirm']}" class="inputtxt" maxlength="255" /></td>
				</tr>
				<tr>
					<th class="addedit"><label for="name">Name: <span class="required">*</span></label></th>
					<td class="addedit"><input type="text" name="name" id="name" value="{$formdatah['name']}" class="inputtxt" maxlength="255" /></td>
				</tr>
			</table>
		</div>

{$nav_html}

	</form>

</div>

EOHTML;


//$headeraddin_html = <<<EOHTML
//<script src="{$cfg['site_url']}resources/admin_user_addedit/admin_user_addedit.js" type="text/javascript"></script>
//EOHTML;

$template = new template();
$template->setmainnavsection('user');
$template->settitle('User > Add/Edit');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>