<?php

class template {

	private $body_html = '';
	private $title = '';
	private $headeraddin_html = '';
	private $mainnavsection = '';
	//private $subnavsection = '';
	private $page = '';
	private $metadesc = '';
	private $usejquerysvg = false;

	public function __construct() {
		global $current_page;

		$this->page = $current_page;
	}

	public function setbodyhtml($body_html) {
		$this->body_html = $body_html;
	}

	public function settitle($title) {
		$this->title = $title;
	}

	public function setmetadesc($metadesc) {
		$this->metadesc = $metadesc;
	}

	public function setheaderaddinhtml($headeraddinhtml) {
		$this->headeraddin_html = $headeraddinhtml;
	}

	public function setpage($page) {
		$this->page = $page;
	}

	public function setmainnavsection($mainnavsection) {
		$this->mainnavsection = $mainnavsection;
	}

	//public function setsubnavsection($subnavsection) {
	//	$this->subnavsection = $subnavsection;
	//}

	public function usejquerysvg($usejquerysvg) {
		$this->usejquerysvg = $usejquerysvg;
	}

	public function display() {
		global $cfg, $authinfo, $current_page, $db, $tbl, $self, $pageself;

		$title_html = htmlentities($cfg['site_name']);

		if ($this->title) {
			$title_html .= ' - ' . htmlentities($this->title);
		} else {
			$title_html .= '';
		}

		$link_base_path = htmlentities(navfr::base_path());

		$self_h = htmlentities($cfg['site_url']);

		if (isset($authinfo['id'])) {

			$navigation = array(
				'livemap' => array(
					'name' => 'Live Map',
					'link' => navpd::link_h(array('p' => 'livemap')),
				),
				'tag' => array(
					'name' => 'Tags',
					'link' => navpd::link_h(array('p' => 'tag')),
				),
				'reader' => array(
					'name' => 'Readers',
					'link' => navpd::link_h(array('p' => 'reader')),
				),
				'map' => array(
					'name' => 'Maps',
					'link' => navpd::link_h(array('p' => 'map')),
				),
				'user' => array(
					'name' => 'Users',
					'link' => navpd::link_h(array('p' => 'user')),
				),
				'logout' => array(
					'name' => 'Logout',
					'link' => navpd::link_h(array('logout' => 1)),
				),
			);

			$nav_main_html = '';
			$nav_sub_html = '';

			foreach ($navigation as $navitem_id => $navitem) {

				if ( ($navitem_id == $current_page) || ($navitem_id == $this->mainnavsection) ) {
					$class = 'selected';
				} else {
					$class = 'nonselected';
				}

				$nav_main_html .= <<<EOHTML
<a href="{$navitem['link']}" class="{$class}">{$navitem['name']}</a>
EOHTML;

			}

		} else {
			$nav_main_html = '';
		}

		//Metatags

		$metatags = array(
			'description' => $this->metadesc,
			'keywords' => '',
		);

		$metatags_html = '';
		foreach ($metatags as $name => $value) {

			if ($value) {

				$value_h = htmlentities($value);

				$metatags_html .= <<<EOHTML
<meta name="{$name}" content="{$value_h}" />
EOHTML;

			}

		}

		if ($this->usejquerysvg == true) {
			$headeraddin_html = <<<EOHTML
<!--//http://keith-wood.name/svg.html//-->
<script type="text/javascript" src="{$link_base_path}resources/template/javascript/jquery/jquery-1.3.2.js"></script>
<script type="text/javascript" src="{$link_base_path}resources/template/javascript/jquery/jquery.svg.js"></script>
<script type="text/javascript" src="{$link_base_path}resources/template/javascript/jquery/jquery.svganim.js"></script>

{$this->headeraddin_html}
EOHTML;
		} else {
			$headeraddin_html = $this->headeraddin_html;
		}

		//<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		//<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		//<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

		//<script src="resources/javascript/library.js" language="javascript" type="text/javascript"></script>

		$page_html = <<<EOHTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>{$title_html}</title>

<link rel="stylesheet" type="text/css" href="{$link_base_path}resources/template/css/reset.css">
<link rel="stylesheet" type="text/css" href="{$link_base_path}resources/template/css/general.css">
<link rel="stylesheet" type="text/css" href="{$link_base_path}resources/template/css/jquery.svg.css">

<!--[if IE 6]>
<link rel="stylesheet" type="text/css" href="{$link_base_path}resources/template/css/ie6.css" />
<![endif]-->

<script src="{$cfg['site_url']}resources/template/javascript/library.js" type="text/javascript"></script>
<script src="{$cfg['site_url']}resources/template/javascript/general.js" type="text/javascript"></script>

{$metatags_html}

{$headeraddin_html}

<div class="page-{$this->page}">

	<div class="wrapper">

		<div class="header">

			<h1><a href="{$cfg['site_url']}">{$cfg['site_name']}</a></h1>

		</div>

		<div class="pagenavigation">

{$nav_main_html}

		</div>

		<div class="bodycontent">

{$this->body_html}

		</div>

	</div>
</div>

</body>
</html>
EOHTML;


		header("Content-Type: text/html; charset=ISO-8859-1");

		//If browser supports compressed encoding, and not in dev mode, and is enable in config then use it
		if ( (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) && ($cfg['devmode'] == false) && ($cfg['output_compressions'] == true) ) {

			header("X-Compression: gzip");
			header("Content-Encoding: gzip");
			//header("Content-Length: " . filesize());
			echo gzencode($page_html);

		} else {
			echo $page_html;
		}

	}

}

?>