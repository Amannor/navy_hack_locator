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


$link_base_path = htmlentities(navfr::base_path());

define('PAGE_TYPE_ADD', 1);
define('PAGE_TYPE_EDIT', 2);

if (isset($_GET['editid'])) {
	$pagetype = PAGE_TYPE_EDIT;
	$editid = intval($_GET['editid']);
	$title = 'Edit Map';
} else {
	$pagetype = PAGE_TYPE_ADD;
	$title = 'Add Map';
}

$errormsg_html = array();

if (isset($_POST['formposted'])) {

	if (!$_POST['name']) {
		$errormsg_html[] = "'Name' not specified.";
	}

	/*
	if ($_POST['width_px']) {
		if (preg_match("%[^0-9]%", $_POST['width_px'])) {
			$errormsg_html[] = "'Width' must be numeric";
		}
	} else {
		$errormsg_html[] = "'Width' not specified.";
	}

	if ($_POST['height_px']) {
		if (preg_match("%[^0-9]%", $_POST['xpos'])) {
			$errormsg_html[] = "'Height' must be numeric";
		}
	} else {
		$errormsg_html[] = "'Height' not specified.";
	}
	*/

	//Check not a duplicate name / navname
	$cond_base = array();
	if ($pagetype == PAGE_TYPE_EDIT) {
		$cond_base[] = "id != {$editid}";
	}

	//Check for duplicate name
	$cond = array_merge($cond_base, array("name = '".$db->es($_POST['name'])."'"));
	$map_result = $db->table_query($db->tbl($tbl['map']), $db->col(array('id', 'name')), $db->cond($cond, 'AND'), '', 0, 1);
	if ($map_record = $db->record_fetch($map_result)) {
		$errormsg_html[] = "'Name' has already been used by Map &quot;".htmlentities($map_record['name'])."&quot;";
	}

	if ($cfg['system_mode_demo'] == true) {
		$errormsg_html[] = 'Feature not available in demo mode';
	}

	if (count($errormsg_html) == 0) {

		//Map Image Upload
		$map_image_valid = false;
		if ( (isset($_FILES['map_image'])) && ($_FILES['map_image']['error'] != 4) ) {
			if ($_FILES['map_image']['error'] == 0) {
				if ($_FILES['map_image']['size'] <= $cfg['map_file_maxsize']) {
					if (preg_match("/(.*)\.(png)$/", $_FILES['map_image']['name'])) {
						if ($_FILES['map_image']['type'] == 'image/png') {
							$map_image_valid = true;
						} else {
							$errormsg_html[] = "Error: Map file type not supported, must be: .png";
						}
					} else {
						$errormsg_html[] = "Error: Map file type not supported, must be: .png";
					}
				} else {
					$errormsg_html[] = "Error: Map maximum limit of {$cfg['map_file_maxsize']} bytes per file";
				}
			} else {
				$errormsg_html[] = "Error: Unable to upload map (code: {$_FILES['map_image']['error']})";
			}
		} else {

			if ($pagetype == PAGE_TYPE_ADD) {
				$errormsg_html[] = "Error: 'Map' not specified";
			}

		}

		//If have valid map upload
		if ($map_image_valid) {

			$imagesize = getimagesize($_FILES['map_image']['tmp_name']);
			if ($imagesize !== false) {

				$imagesize['w'] = $imagesize[0];
				$imagesize['h'] = $imagesize[1];

				if (!( ($imagesize['w'] >= $cfg['map_width_min']) && ($imagesize['w'] <= $cfg['map_width_max']) )) {
					$errormsg_html[] = "Error: Map width must be {$cfg['map_width_min']}-{$cfg['map_width_max']} px wide";
				}

				if (!( ($imagesize['h'] >= $cfg['map_height_min']) && ($imagesize['h'] <= $cfg['map_height_max']) )) {
					$errormsg_html[] = "Error: Map height must be {$cfg['map_height_min']}-{$cfg['map_height_max']} px heigh";
				}

				if ($pagetype == PAGE_TYPE_EDIT) {

					$map_result = $db->table_query($db->tbl($tbl['map']), $db->col(array('width_px', 'height_px')), $db->cond(array("id = {$editid}"), 'AND'), '', 0, 1);
					if (!($map_record = $db->record_fetch($map_result))) {
						throw new Exception("Unable to retrieve details for map id \"{$editid}\"");
					}

					if (!( ($map_record['width_px'] == $imagesize['w']) && ($map_record['height_px'] == $imagesize['h']) )) {
						$errormsg_html[] = "Error: Map width/height ({$imagesize['w']}x{$imagesize['h']}) do not match width/height ({$map_record['width_px']}x{$map_record['height_px']}) of map being replaced.";
					}

				}

			} else {
				$errormsg_html[] = "Error: Image format not recognised.";
			}

		}

	}

	//If no errors
	if (count($errormsg_html) == 0) {

		//$db->transaction(dbmysql::TRANSACTION_START);

		$record = array(
			'name' => $_POST['name'],
			//'width_px' => $_POST['width_px'],
			//'height_px' => $_POST['height_px'],
		);

		$record = appgeneral::filternonascii_array($record);

		if ($map_image_valid) {
			$record['width_px'] = $imagesize['w'];
			$record['height_px'] = $imagesize['h'];
		}

		if ($pagetype == PAGE_TYPE_EDIT) {

			//$record = array_merge($record, array('lastupdated' => $db->datetimenow()));
			$db->record_update($tbl['map'], $db->rec($record), $db->cond(array("id = {$editid}"), 'AND'));

			$map_image_path = $cfg['maps_dir_path'] . $editid . '.png';

		} else {

			//$record = array_merge($record, array('added' => $db->datetimenow(), 'lastupdated' => $db->datetimenow()));
			$db->record_insert($tbl['map'], $db->rec($record));
			$editid = $db->record_insert_id();
			$pagetype = PAGE_TYPE_EDIT;

			$map_image_path = $cfg['maps_dir_path'] . $editid . '.png';

		}

		if ($map_image_valid) {

			//Save uploaded logo
			if (file_exists($map_image_path)) {
				unlink($map_image_path);
			}

			$status = move_uploaded_file($_FILES['map_image']['tmp_name'], $map_image_path);
			if (!$status) {
				throw new Exception("Unable to move map file to \"{$map_image_path}\"");
			}

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
		$map_result = $db->table_query($db->tbl($tbl['map']), $db->col(array('name', 'width_px', 'height_px')), $db->cond(array("id = {$editid}"), 'AND'), '', 0, 1);
		if (!($map_record = $db->record_fetch($map_result))) {
			throw new Exception("Specified edit id \"{$editid}\" not found");
		}

		$formdata = $map_record;

	} else {

		$formdata = array(
			'name' => '',
			//'width_px' => '',
			//'height_px' => '',
		);

	}

}


$self_args = array();

if ($pagetype == PAGE_TYPE_EDIT) {
	$self_args = array('editid' => $editid);
}


$link_h = navpd::self($self_args);

$formdatah = lib::htmlentities_array($formdata);

//If this is an edit
if ( ($pagetype == PAGE_TYPE_EDIT) && (file_exists($cfg['maps_dir_path'] . "{$editid}.png")) ){

	//Retrieve table data
	$map_result = $db->table_query($db->tbl($tbl['map']), $db->col(array('name', 'width_px', 'height_px')), $db->cond(array("id = {$editid}"), 'AND'), '', 0, 1);
	if (!($map_record = $db->record_fetch($map_result))) {
		throw new Exception("Specified edit id \"{$editid}\" not found");
	}

	/*
	if ($map_record['width_px'] > 800) {
		$width = $map_record['width_px'];
		$height = $map_record['height_px'];
	} else {
		$width = $map_record['width_px'];
		$height = $map_record['height_px'];
	}
	*/

	$width = $map_record['width_px'];
	$height = $map_record['height_px'];

	$logo_image_preview = <<<EOHTML
<br />
<br />
<a href="{$link_base_path}{$cfg['maps_dir_path']}{$editid}.png" target="_blank"><img src="{$link_base_path}{$cfg['maps_dir_path']}{$editid}.png" width="{$width}" height="{$height}" alt="Map Preview" /></a>
EOHTML;

} else {
	$logo_image_preview = '';
}

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

	<form method="post" action="{$link_h}" id="mainform" class="mainform" enctype="multipart/form-data">
		<div><input type="hidden" name="formposted" value="1" /></div>

{$nav_html}

{$errormsgs_html}

		<div class="tablecontainer">
			<table cellspacing="0" class="addedit">
				<tr>
					<th class="addedit"><label for="name">Name: <span class="required">*</span></label></th>
					<td class="addedit"><input type="text" name="name" id="name" value="{$formdatah['name']}" class="inputtxt" maxlength="255" /></td>
				</tr>
				<!--//
				<tr>
					<th class="addedit"><label for="width_px">Width (px): <span class="required">*</span></label></th>
					<td class="addedit"><input type="text" name="width_px" id="width_px" value="{formdatah['width_px']}" class="inputtxt" maxlength="4" /></td
				</tr>
				<tr>
					<th class="addedit"><label for="height_px">Height (px): <span class="required">*</span></label></th>
					<td class="addedit"><input type="text" name="height_px" id="height_px" value="{formdatah['height_px']}" class="inputtxt" maxlength="4" /></td
				</tr>
				//-->
				<tr>
					<th class="addedit"><label for="map_image">Map Image (.png): <span class="required">*</span></label></th>
					<td class="addedit">
						<input type="file" name="map_image" id="map_image" size="20" />
						{$logo_image_preview}
					</td>
				</tr>
			</table>
		</div>

{$nav_html}

	</form>

</div>

EOHTML;


$template = new template();
$template->setmainnavsection('map');
$template->settitle('Map > Add/Edit');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>