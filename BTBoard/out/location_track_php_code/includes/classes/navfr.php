<?php

class navfr {

	static public function self($args=array()) {
		global $cfg;

		$current_path = self::current_path();

		$selflink = self::base_path() . self::path_url($current_path) . self::args_querystring($args);

		return $selflink;

	}

	static public function link($path_parts, $args=array()) {

		$selflink = self::base_path() . self::path_url($path_parts) . self::args_querystring($args);

		return $selflink;

	}

	static public function back($rmpart=null, $args=array()) {

		$current_path = self::current_path();

		if ($rmpart === null) {
			$rmpart = 0;
		}

		$path_parts_count = count($current_path);

		if ($path_parts_count < $rmpart) {
			throw new Exception("Path consists of \"{$path_parts_count}\" part(s), can not remove \"{$rmpart}\" part(s)");
		}

		for ($i=0; $i<=$rmpart; $i++) {
			array_pop($current_path);
		}

		$backlink = self::link($current_path, $args);

		return $backlink;

	}

	static public function forward($path_parts, $args=array()) {

		$current_path = self::current_path();

		$path_parts_combined = array_merge($current_path, $path_parts);

		$forwardlink = self::link($path_parts_combined, $args);

		return $forwardlink;

	}

	static public function self_h() {
		$func_get_args = func_get_args();
		return htmlentities(call_user_func_array(array('self', 'self'), $func_get_args));
	}

	static public function link_h() {
		$func_get_args = func_get_args();
		return htmlentities(call_user_func_array(array('self', 'link'), $func_get_args));
	}

	static public function back_h() {
		$func_get_args = func_get_args();
		return htmlentities(call_user_func_array(array('self', 'back'), $func_get_args));
	}

	static public function forward_h() {
		$func_get_args = func_get_args();
		return htmlentities(call_user_func_array(array('self', 'forward'), $func_get_args));
	}

	static public function fqlink($link) {
		global $cfg;

		return self::fqname() . $link;

	}

	static public function fqname() {
		global $cfg;

		$parse_url = parse_url($cfg['site_url']);

		$fqname = $parse_url['scheme'] . '://';

		if ( (isset($parse_url['user'])) || (isset($parse_url['pass'])) ) {

			if (isset($parse_url['user'])) {
				$fqname .= $parse_url['user'];
			}

			if (isset($parse_url['pass'])) {
				$fqname .= ':' . $parse_url['pass'];
			}

			$fqname .= '@';
		}

		$fqname .= $parse_url['host'];

		if (isset($parse_url['port'])) {
			$fqname .= ':' . $parse_url['port'];
		}

		return $fqname;

	}

	static public function fqlink_h() {
		$func_get_args = func_get_args();
		return htmlentities(call_user_func_array(array('self', 'fqlink'), $func_get_args));
	}

	static public function base_path() {
		global $cfg;

		$base_path = parse_url($cfg['site_url'], PHP_URL_PATH);

		//$base_path = preg_replace("/^\//", '', $base_path);//Remove leading slash

		return $base_path;

	}

	static public function current_path() {

		if (isset($_GET['path'])) {
			$path = $_GET['path'];
		} else {
			$path = '';
		}

		$path_parts = explode('/', $path);

		$lastindex = count($path_parts) - 1;
		if (!$path_parts[$lastindex]) {
			unset($path_parts[$lastindex]);
		}

		return $path_parts;

	}

	static public function path_url($path_parts) {

		$path = '';
		foreach ($path_parts as $path_part) {
			$path .= rawurlencode($path_part) . '/';
		}

		return $path;

	}

	static public function retr_part($part) {

		$current_path = self::current_path();

		$path_parts = count($current_path);

		if ($path_parts < 1) {
			throw new Exception('Path not found');
		}

		for ($i=0; $i<=$part; $i++) {
			$retr_part = array_pop($current_path);
		}

		if (!isset($retr_part)) {
			throw new Exception("Path part \"{$part}\" not found");
		}

		return $retr_part;

	}

	static public function args_querystring($args) {

		if (count($args) > 0) {
			$querystring = '?' . http_build_query($args);
		} else {
			$querystring = '';
		}

		return $querystring;

	}

}

?>