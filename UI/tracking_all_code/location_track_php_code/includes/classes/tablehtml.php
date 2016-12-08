<?php

/*

//Delete customer
if ( (isset($_POST['actiontype'])) && ($_POST['actiontype'] == 'delete') ) {
	$db->record_delete($tbl['example'], $db->cond(array("id = ".intval($_POST['actionid'])), 'AND'));
}

$link_addnew = navpd::forward(array('p' => 'example_addedit'));
$btn_addnew = btn::create('Add ....', btn::TYPE_LINK, $link_addnew, $cfg['btn_template_path'].'icons/add.png', '', 'Add ....');
$right_html = $btn_addnew;

//Table
$tablehtml = new tablehtml();
$tablehtml->shotbuttoncol = true;
//$tablehtml->sortby = 'name';
//$tablehtml->table_class = 'examplelist';
$tablehtml->sortable_columns = array('name');
$tablehtml->parsegetvars($_GET);

$tablehtml->addcolumn('name', 'Name');
$tablehtml->addcolumn('email', 'Email');
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

	$result = $db->table_query($db->tbl($tbl['customer']), $db->col(array('id', 'name')), $db->cond(array(), 'AND'), $db->order($query_order), $limit_offset, $limit_count, dbmysql::TBLQUERY_FOUNDROWS);

	$tabledatahtml = array();
	while ($data = $db->record_fetch($result)) {

		$datah = lib::htmlentities_array($data);

		//Delete button
		$name_js = addslashes($datah['name']);
		$link_self = addslashes(navpd::self_h());
		$onclick_js = "performaction('{$link_self}', 'Really delete example \'{$name_js}\'?', 'delete', {$data['id']}); return false;";
		$btn_delete = btn::create('Delete', btn::TYPE_LINK, '#', $cfg['btn_template_path'].'icons/delete.png', $onclick_js, "Delete example \"{$name_js}\"");

		//Edit button
		$link_edit = navpd::forward(array('p' => 'example_addedit', 'editid' => $data['id']));
		$btn_edit = btn::create('Edit', btn::TYPE_LINK, $link_edit, $cfg['btn_template_path'].'icons/edit.png', '', 'Edit');

		$buttons = $tablehtml->html_table_buttons(array($btn_edit, $btn_delete));

		$tabledatahtml[] = array(
			'name' => htmlentities(appgeneral::trim_length($data['name'], 10)),
			'added' => date('Y-m-d H:i:s', strtotime($datah['added'] . ' UTC')),
			'email' => $datah['email'],
			'button' => $buttons,
		);

	}

	$tablehtml->paging_totrows = $db->query_foundrows();

	return $tabledatahtml;

}

$body_html = <<<EOHTML

{$table_html}

EOHTML;

*/

class tablehtml {

	public $columns = array();
	public $table_class = '';

	public $paging_totrows = 0;
	public $paging_currpage = 1;
	public $paging_rowperpage = 10;

	public $sortby = '';
	public $sortdir = 'asc';
	public $sortable_columns = array();

	public $errormsgs = array();

	//Add column to table
	public function addcolumn($alias, $name) {

		$column = array(
			'name' => $name,
			//'width' => $width,
		);
		$this->columns[$alias] = $column;

	}

	//Parse get vars
	public function parsegetvars($getvars) {

		//Current page
		if (isset($getvars['i'])) {
			$this->paging_currpage = intval($getvars['i']);
		}

		//Sorting
		if ( (isset($getvars['sortdir'])) || (isset($getvars['sortby'])) ) {

			if ( (isset($getvars['sortdir'])) && (isset($getvars['sortby'])) ) {

				if ( ($getvars['sortdir'] == 'asc') || ($getvars['sortdir'] == 'desc') ) {

					$this->sortdir = $getvars['sortdir'];
					$this->sortby = $getvars['sortby'];

					//Check sort by valid
					if (!in_array($this->sortby, $this->sortable_columns)) {
						throw new Exception("'sortby' is not valid");
					}

				} else {
					throw new Exception("'sortdir' of \"{$getvars['sortdir']}\" not valid");
				}
			} else {
				throw new Exception("Both 'sortdir' and 'sortby' must be specified");
			}

		}

	}

	//Retrieve titles html
	public function html_table_titles() {
		global $cfg;

		$html = '';
		foreach ($this->columns as $colalias => $column) {

			$nameh = htmlentities($column['name']);
			$fieldh = $nameh;

			$tdaddin = $this->html_table_tdaddin($colalias, $column);

			if (in_array($colalias, $this->sortable_columns)) {

				$sortdir = ( ($this->sortdir == 'desc') || ($this->sortby != $colalias) ) ? 'asc' : 'desc';

				if ($this->sortby == $colalias) {

					$sortcol = 'b'; //w|b

					if ($this->sortdir == 'asc') {

						//$sortbyh = '/\\';
						$sortbyh = <<<EOHTML
 <img src="{$cfg['site_url']}resources/maintable/sort_{$sortcol}_desc.gif" alt="Sorted descending">
EOHTML;

						$titleh = ' title="Change to descending"';
					} else {

						//$sortbyh = '\\/';
						$sortbyh = <<<EOHTML
 <img src="{$cfg['site_url']}resources/maintable/sort_{$sortcol}_asc.gif" alt="Sorted ascending">
EOHTML;

						$titleh = ' title="Change to ascending"';
					}

				} else {
					$titleh = '';
					$sortbyh = '';
				}

				if ($fieldh) {
					$link = navpd::self_h(array('i' => 1, 'sortby' => $colalias, 'sortdir' => $sortdir));
					$fieldh = <<<EOHTML
<a href="{$link}"{$titleh}>{$fieldh}{$sortbyh}</a>
EOHTML;
				}

			}

			$html .= <<<EOHTML
\t\t<th{$tdaddin}>{$fieldh}</th>

EOHTML;

		}

		if ($html) {

			$html = rtrim($html);

			$html = <<<EOHTML
\t<tr class="titles">
{$html}
\t</tr>
EOHTML;
		}

		return $html;

	}
	
	//Retrieve rows html
	public function html_table_rows($tabledatahtml) {

		if (count($tabledatahtml) > 0) {

			$html = '';
			foreach ($tabledatahtml as $row) {

				$cols_html = '';
				foreach ($this->columns as $colalias => $column) {

					$tdaddin = $this->html_table_tdaddin($colalias, $column);

					$cols_html .= <<<EOHTML
\t\t<td{$tdaddin}>{$row[$colalias]}</td>

EOHTML;

				}

				$cols_html = rtrim($cols_html);

				$html .= <<<EOHTML
\t<tr>
{$cols_html}
\t</tr>

EOHTML;

			}

			$html = rtrim($html);

		} else {

			$columns_total = count($this->columns);

			$html = <<<EOHTML
\t<tr>
\t\t<td colspan="{$columns_total}" class="norecords">No records available</td>
\t</tr>
EOHTML;

		}

		return $html;

	}

	//HTML table td addin
	function html_table_tdaddin($colalias, $column) {

		$html = '';

		/*
		if ( (isset($column['width'])) && ($column['width']) ) {
			$html .= ' width="'.$column['width'].'"';
		}
		*/

		$html .= ' class="col-'.$colalias.'"';

		return $html;

	}

	//Retrieve table data from callback
	function tabledatahtml_fromcallback($callback_function) {

		//If page greater than zero
		if ($this->paging_currpage > 0) {

			//Calculate start record for query
			$limit_offset = intval(($this->paging_currpage-1) * $this->paging_rowperpage);

			$limit_count = $this->paging_rowperpage;

			if ($this->sortby) {
				$query_order = array(array($this->sortby, strtoupper($this->sortdir)));
			} else {
				$query_order = array();
			}

			//Retrieve data
			$tabledatahtml = call_user_func($callback_function, $this, $limit_offset, $limit_count, $query_order);

			if ($limit_offset > $this->paging_totrows) {
				$this->errormsgs[] = 'Page No not valid';
			}

		} else {
			$this->errormsgs[] = 'Page No not valid';
			$tabledatahtml = array();
		}

		return $tabledatahtml;

	}

	//Retrieve errors html
	public function html_table_errors() {

		$columns_total = count($this->columns);

		$html = '';
		foreach ($this->errormsgs as $errormsg) {

			$errormsgh = htmlentities($errormsg);

/*
			$html .= <<<EOHTML
\t<tr>
\t\t<td colspan="{$columns_total}" class="errormsg"><strong>Error:</strong> {$errormsgh}</td>
\t</tr>
EOHTML;
*/

			$html .= <<<EOHTML
<div class="errormsg"><strong>Error:</strong> {$errormsgh}</div>

EOHTML;

		}

		return $html;

	}

	//Retrieve navigation html
	public function html_table_nav($right_html='', $left_html='') {

		//Get total number of pages
		$totalpages = ceil($this->paging_totrows / $this->paging_rowperpage);

		$columns_total = count($this->columns);

		if ( ($this->paging_currpage == 1) || ( ($this->paging_currpage >= 1) && ($this->paging_currpage <= $totalpages) ) ) {

			if ($this->paging_totrows != 0) {
				$currpage_html = <<<EOHTML
Page {$this->paging_currpage} of {$totalpages}
EOHTML;
			} else {
				$currpage_html = '';
			}

			if ( ($this->paging_currpage > 1) && ($this->paging_currpage <= $totalpages) ) {
				$btn_first = btn::create('<< First', btn::TYPE_LINK, navpd::self(array('i' => 1)), '', '', 'First Page');
				$btn_prev = btn::create('< Prev', btn::TYPE_LINK, navpd::self(array('i' => $this->paging_currpage-1)), '', '', 'Previous Page');
			} else {
				$btn_first = btn::create_nolink('<< First', '', 'First Page');
				$btn_prev = btn::create_nolink('< Prev', '', 'Previous Page');
			}

			if ($this->paging_currpage < $totalpages) {
				$btn_last = btn::create('Last >>', btn::TYPE_LINK, navpd::self(array('i' => $totalpages)), '', '', 'Last Page');
				$btn_next = btn::create('Next >', btn::TYPE_LINK, navpd::self(array('i' => $this->paging_currpage+1)), '', '', 'Next Page');
			} else {
				$btn_last = btn::create_nolink('Last >>', '', 'Last Page');
				$btn_next = btn::create_nolink('Next >', '', 'Next Page');
			}

			$firstprev_html = <<<EOHTML
					{$btn_first}
					&nbsp;
					{$btn_prev}
EOHTML;

			$nextlast_html = <<<EOHTML
					{$btn_next}
					&nbsp;
					{$btn_last}
EOHTML;

		} else {
			$currpage_html = '';
			$firstprev_html = btn::create('back to first', btn::TYPE_LINK, navpd::self(array('i' => 1)), '', '', 'Back to first page');
			$nextlast_html = '';
		}

		$html = <<<EOHTML

<div class="navigation">

	<div class="nav-left">{$left_html}</div>
	<div class="nav-right">{$right_html}</div>
	<div class="nav-paging">

		<div class="firstprev">
{$firstprev_html}
		</div>

		<div class="nextlast">
{$nextlast_html}
		</div>

		<div class="currpage">
			{$currpage_html}
		</div>

	</div>

</div>

EOHTML;

		return $html;

	}

	//Retrieve table html
	public function html_table($titles_html, $rows_html, $navigation_html, $errors_html) {

		if ($this->table_class) {
			$tableclassaddin = ' ' . $this->table_class;
		} else {
			$tableclassaddin = '';
		}

		$html = <<<EOHTML

<div class="maintable{$tableclassaddin}">

{$navigation_html}
{$errors_html}

	<table cellspacing="0">
{$titles_html}
{$rows_html}
	</table>

{$navigation_html}

</div>

EOHTML;

		return $html;

	}

	//Retrieve action handling html
	public function html_action() {

		$html = <<<EOHTML

<form method="post" action="#" id="frm_action">
	<div><input type="hidden" id="actiontype" name="actiontype" /></div>
	<div><input type="hidden" id="actionid" name="actionid" /></div>
</form>

<script type="text/javascript">
  //<![CDATA[

	//Perform standard single action, eg delete record
	function performaction(posturl, confirmtext, action, idvalue) {

		if ( ( (confirmtext) && (confirm(confirmtext)) ) || (!confirmtext) ) {
			$("frm_action").action = posturl;
			$("actiontype").value = action;
			$("actionid").value = idvalue;
			$("frm_action").submit();
		}

	}

  //]]>
</script>

EOHTML;

		return $html;

	}

	//Retrieve html for table
	public function html($action_html, $table_html) {

		$html = <<<EOHTML
{$action_html}
{$table_html}
EOHTML;

		return $html;

	}

	//Retrieve buttons html
	public function html_table_buttons($buttons) {

		$html = implode($buttons, ' ');

		return $html;

	}

}

?>