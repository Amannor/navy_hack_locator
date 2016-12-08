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


$errormsgs_html = array();

//Delete tag
if ( (isset($_POST['actiontype'])) && ($_POST['actiontype'] == 'delete') ) {

	if ($cfg['system_mode_demo'] == false) {
		$db->record_delete($tbl['tag'], $db->cond(array("id = ".intval($_POST['actionid'])), 'AND'));
	} else {
		$errormsgs_html[] = 'Feature not available in demo mode';
	}

}

$link_addnew = navpd::forward(array('p' => 'tag_addedit'));
$btn_addnew = btn::create('Add Tag', btn::TYPE_LINK, $link_addnew, $cfg['btn_template_path'].'icons/add.png', '', 'Add Tag');
$right_html = $btn_addnew;


//Table
$tablehtml = new tablehtml();
$tablehtml->shotbuttoncol = true;
$tablehtml->sortby = 'name';
$tablehtml->sortdir = 'asc';
//$tablehtml->table_class = 'examplelist';
$tablehtml->sortable_columns = array('name', 'addr');
$tablehtml->parsegetvars($_GET);
$tablehtml->errormsgs = array_merge($tablehtml->errormsgs, $errormsgs_html);

//$tablehtml->addcolumn('checkbox', '');
$tablehtml->addcolumn('name', 'Name');
$tablehtml->addcolumn('addr', 'Node Address');
$tablehtml->addcolumn('colour', 'Colour');
$tablehtml->addcolumn('button', '');

$table_html = $tablehtml->html(
	$tablehtml->html_action(),
	$tablehtml->html_table(
		$tablehtml->html_table_titles(),
		$tablehtml->html_table_rows(
			$tablehtml->tabledatahtml_fromcallback('callback_tabledatahtml')
		),
		$tablehtml->html_table_nav($right_html),
		$tablehtml->html_table_errors()
	)
);

function callback_tabledatahtml($tablehtml, $limit_offset, $limit_count, $query_order) {
	global $cfg, $tbl, $db;

	$select_sql = $db->col(array('id', 'name', 'addr', 'colour'));
	$result = $db->table_query($db->tbl($tbl['tag']), $select_sql, $db->cond(array(), 'AND'), $db->order($query_order), $limit_offset, $limit_count, dbmysql::TBLQUERY_FOUNDROWS);

	$tabledatahtml = array();
	while ($data = $db->record_fetch($result)) {

		$datah = lib::htmlentities_array($data);

		//Delete button
		$name_js = addslashes($datah['name']);
		$link_self = addslashes(navpd::self());
		$onclick_js = "performaction('{$link_self}', 'Really delete tag \'{$name_js}\'?', 'delete', {$data['id']}); return false;";
		$btn_delete = btn::create('Delete', btn::TYPE_LINK, '#', $cfg['btn_template_path'].'icons/delete.png', $onclick_js, "Delete tag \"{$name_js}\"");

		//Edit button
		$link_edit = navpd::forward(array('p' => 'tag_addedit', 'editid' => $data['id']));
		$btn_edit = btn::create('Edit', btn::TYPE_LINK, $link_edit, $cfg['btn_template_path'].'icons/edit.png', '', 'Edit');

		$buttons = $tablehtml->html_table_buttons(array($btn_edit, $btn_delete));

		$tabledatahtml[] = array(
			//'checkbox' => '<input type="checkbox" name="actionids[]" value="'.$data['id'].'">',
			//'checkbox' => '',
			'name' => htmlentities(appgeneral::trim_length($data['name'], 30)),
			'addr' => htmlentities($data['addr']),
			'colour' => '<span style="color: '.$data['colour'].'">'.htmlentities($data['colour']) . '</span>',
			'button' => $buttons,
		);

	}

	$tablehtml->paging_totrows = $db->query_foundrows();

	return $tabledatahtml;

}

$body_html = <<<EOHTML

{$table_html}

EOHTML;


$template = new template();
$template->setmainnavsection('tag');
$template->settitle('Tags');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>