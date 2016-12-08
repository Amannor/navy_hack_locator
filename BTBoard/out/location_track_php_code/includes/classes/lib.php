<?php

/*

lib::create_options($options);

lib::prh();

echo lib::randstring(10, lib::RANDSTRING_AZ_LC | lib::RANDSTRING_AZ_UC | lib::RANDSTRING_09);

lib::array_jsarray('jsarray', $array));

lib::chkemailvalid($email);

lib::htmlentities_array($array);

lib::create_radio('radioname', $options, 'checked');

lib::create_options($options);

lib::create_options($options, array('0',''), lib::CREATEOPT_PLEASESELECT)

lib::emailtoarray($email);

lib::cache_control($cache_day=0, $cache_min=0, $cache_hour=0);

lib::array_formfield($formfield);

lib::dirlist($folder, lib::DIRLIST_FILE | lib::DIRLIST_FOLDER);

*/

class lib {

	const CREATERADIO_NEWLINE = 1;

	const CREATEOPT_PLEASESELECT = 1;

	const RANDSTRING_AZ_LC = 1;
	const RANDSTRING_AZ_UC = 2;
	const RANDSTRING_09 = 4;

	const DIRLIST_FILE = 1;
	const DIRLIST_FOLDER = 2;

	//Print html safe array
	static public function prh($array) {
		echo "<pre>".htmlentities(print_r($array, true))."</pre>";
	}

	//htmlentities escape array
	static public function htmlentities_array($array) {

		if (!is_array($array)) {
			throw new Exception("\"{$array}\" is not an array");
		}

		$arraynew = array();
		foreach ($array as $name => $value) {
			if (is_array($value)) {
				$arraynew[$name] = self::htmlentities_array($value);
			} else {
				$arraynew[$name] = htmlentities($value);
			}
		}

		return $arraynew;

	}

	//Prepare empty formdata
	static public function prepare_formdata($fields) {

		$formdata = array();
		foreach ($fields as $field) {
			$formdata[$field] = '';
		}

		return $formdata;

	}

	//Create dropdown box options html
	static public function create_options($options, $selected=null, $extraoptions='') {

		$optionvalues = self::bitwiseopt_toarray($extraoptions);

		$selected_list = array();
		if ($selected !== null) {
			if (is_array($selected)) {
				$selected_list = $selected;
			} else {
				$selected_list[] = $selected;
			}
		}

		if (in_array(self::CREATEOPT_PLEASESELECT, $optionvalues)) {
			$optionsnew = array('' => '-- Please Select --');
			foreach ($options as $name => $value) {
				$optionsnew[$name] = $value;
			}
			$options = $optionsnew;
		}

		$options_html = '';
		foreach ($options as $name => $value) {

			$name_h = htmlentities($name);
			$value_h = htmlentities($value);

			$selected_html = '';
			foreach ($selected_list as $selecteditem) {
				if ( ($selecteditem !== null) && (strlen($selecteditem) == strlen($name)) && ($selecteditem == $name) ) {
					$selected_html = ' selected="selected"';
					break;
				}
			}

			$options_html .= <<<EOHTML
<option value="{$name_h}"{$selected_html}>{$value_h}</option>\n
EOHTML;

		}

		return $options_html;

	}

	//Create radio buttons html
	static public function create_radio($grpname, $options, $checked=null, $disabled=null, $class='', $addinhtml='', $extraoptions='') {

		$optionvalues = self::bitwiseopt_toarray($extraoptions);

		$disabled_list = array();
		if ($disabled !== null) {
			if (is_array($disabled)) {
				$disabled_list = $disabled;
			} else {
				$disabled_list[] = $disabled;
			}
		}

		$grpname_h = htmlentities($grpname);

		$curropt = 0;
		$radiooptions_html = '';
		$totalopt = count($options);
		foreach ($options as $name => $value) {

			$name_h = htmlentities($name);
			$value_h = htmlentities($value);

			if ( ($checked !== null) && ($checked == $name) && (strlen($checked) == strlen($name)) ) {
				$checked_html = ' checked="checked"';
			} else{
				$checked_html = '';
			}

			$disabled_html = '';
			foreach ($disabled_list as $disableditem) {
				if ( ($disableditem !== null) && ($disableditem == $name) && (strlen($disableditem) == strlen($name)) ) {
					$disabled_html = ' disabled="disabled"';
					break;
				}
			}

			if (in_array(self::CREATERADIO_NEWLINE, $optionvalues)) {
				$spacer = '<br />';
			} else {
				$spacer = ($curropt+1 != $totalopt) ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : '';
			}

			if ($class) {
				$class_html = " class=\"{$class}\"";
			} else {
				$class_html = '';
			}

			$radiooptions_html .= <<<EOHTML
<input type="radio" name="{$grpname_h}" id="{$grpname_h}_{$name_h}" value="{$name_h}" {$checked_html}{$disabled_html}{$class_html}{$addinhtml} /> &nbsp;<label for="{$grpname_h}_{$name_h}">{$value_h}</label>{$spacer}\n
EOHTML;

			$curropt++;
		}

		return $radiooptions_html;

	}

	//Create random string
	static public function randstring($length, $options) {

		$optionvalues = self::bitwiseopt_toarray($options);

		$length = intval($length);

		$charsetoptions = array(
			self::RANDSTRING_AZ_LC => array('frm' => 97, 'to' => 122),
			self::RANDSTRING_AZ_UC => array('frm' => 65, 'to' => 90),
			self::RANDSTRING_09 => array('frm' => 48, 'to' => 57),
		);

		//Check at least one charset is specified
		if (count($optionvalues) < 1) {
			throw new Exception('At least one charset must be specified');
		}

		//Check string length is specified
		if ($length < 1) {
			throw new Exception('Length must be greater than zero');
		}

		//Go through charsets specified
		$charset_picklist = array();
		foreach ($optionvalues as $charset) {
			//Check specified charset is valid
			if (!isset($charsetoptions[$charset])) {
				throw new Exception("Specified charset \"{$charset}\" invalid");
			}

			//Go though characters in the charset and add them on
			for ($i=$charsetoptions[$charset]['frm']; $i <= $charsetoptions[$charset]['to']; $i++) {
				$charset_picklist[] = chr($i);
			}

		}

		$charset_picklist_total = count($charset_picklist);

		$randstring = '';

		for ($i=0; $i<$length; $i++) {
			$randchrindex = mt_rand(0, $charset_picklist_total-1);
			$randstring .= $charset_picklist[$randchrindex];
		}

		return $randstring;

	}

	//Convert php array to hidden form fields
	static public function array_formfield($formfield, $prefix='') {

		$formfield_html = '';

		foreach ($formfield as $name => $value) {

			if ($prefix) {
				$namewprefix = $prefix . '[' . $name . ']';
			} else {
				$namewprefix = $name;
			}

			if (is_array($value)) {

				$formfield_html .= self::array_formfield($value, $namewprefix);

			} else {

				$value_h = htmlentities($value);

				if ($prefix) {
					$namewprefix_h = htmlentities($namewprefix);
				} else {
					$namewprefix_h = htmlentities($name);
				}

				$formfield_html .= <<<EOHTML
<input type="hidden" name="{$namewprefix_h}" value="{$value_h}" />\n
EOHTML;
			}

		}

		return $formfield_html;

	}

	//Convert php array to js array
	static public function array_jsarray($grpname, $array, $level=0) {

		if ($level > 10) {
			throw new Exception('Too much recursion');
		}

		$array_js = '';
		foreach ($array as $name => $value) {

			$name_s = addslashes($name);

			if (is_array($value)) {

				$array_js_recur = self::array_jsarray('', $value, $level+1);
				$tabs = str_repeat("\t", $level+1);
				$array_js .= <<<EOJS
{$tabs}"{$name_s}": {
{$array_js_recur}
{$tabs}},\n
EOJS;

			} else {

				$value_s = addslashes($value);
				$value_s = str_replace("\r", '', $value_s);
				$value_s = str_replace("\n", '\n', $value_s);
				$value_s = str_replace("\t", '\t', $value_s);

				$tabs = str_repeat("\t", $level+1);

				$array_js .= <<<EOJS
{$tabs}"{$name_s}": "{$value_s}",\n
EOJS;

			}

		}

		$array_js = rtrim($array_js, ",\n");

		if ($level == 0) {

			$array_js = <<<EOJS
{$grpname} = {
{$array_js}
};
EOJS;

		}

		return $array_js;

	}

	//Check email address is valid
	static public function chkemailvalid($email) {

		//http://iamcal.com/publish/articles/php/parsing_email

		if (preg_match("%[^\041-\176]%", $email)) {
			return false;
		}

		$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
		$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
		$atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
		$quoted_pair = '\\x5c[\\x00-\\x7f]';
		$domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
		$quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
		$domain_ref = $atom;
		$sub_domain = "($domain_ref|$domain_literal)";
		$word = "($atom|$quoted_string)";
		$domain = "$sub_domain(\\x2e$sub_domain)*";
		$local_part = "$word(\\x2e$word)*";
		$addr_spec = "$local_part\\x40$domain";

		return preg_match("!^$addr_spec$!", $email) ? true : false;

	}

	//Convert email address string to array
	static public function emailtoarray($email) {
		$emaillist = preg_split('/\,|\;|\ /', $email, -1, PREG_SPLIT_NO_EMPTY);
		return $emaillist;
	}

	//Browser cache control
	static public function cache_control($cache_day=0, $cache_min=0, $cache_hour=0) {
		///http://www.mnot.net/cache_docs/
		$expires = 'Expires: ' . gmdate('D, d M Y H:i:s', time() + ($cache_day * 60 * 60 * 24) + ($cache_min * 60) + ($cache_hour * 60 * 60)) . ' GMT';
		header($expires);
	}

	//Directory listing
	static function dirlist($folder, $extraoptions='') {

		$optionvalues = self::bitwiseopt_toarray($extraoptions);

		$files = array();

		if (!is_dir($folder)) {
			throw new Exception("Specified folder \"{$folder}\" does not exist");
		}

		$dh = opendir($folder);
		while (false !== ($file = readdir($dh))) {

			$ignore = array('.', '..');
			if (!in_array($file, $ignore)){

				if (!$extraoptions) {

					$files[] = $file;
				} else if (in_array(self::DIRLIST_FOLDER, $optionvalues)) {

					if (is_dir($folder . $file)) {
						$files[] = $file;
					}

				} else if (in_array(self::DIRLIST_FILE, $optionvalues)) {

					if (is_file($folder . $file)) {
						$files[] = $file;
					}

				}

			}

		}

		return $files;

	}

	//Convert bitwise "or" options list to array with option values
	//1, 2, 4, 8, 16, 32, 64, 128
	static public function bitwiseopt_toarray($options) {

		$options_list = array();

		if ($options) {

			$binoptions = decbin($options);
			$totaldigit = strlen($binoptions);

			for ($i=1; $i <= $totaldigit; $i++) {
				$option = substr($binoptions, -$i, 1);
				$binoption = str_pad($option, $i, 0, STR_PAD_RIGHT);
				$optionint = bindec($binoption);
				if ($optionint) {
					$options_list[] = bindec($binoption);
				}

			}

		}

		return $options_list;

	}

}

?>