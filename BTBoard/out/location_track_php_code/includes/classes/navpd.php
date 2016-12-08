<?php

/*

navpd::self($self_args=array())
navpd::forward($dest_args=array(), $curr_args=array())
navpd::back($dest_args=array())
navpd::self_form($self_args=array())
navpd::self_h()
navpd::forward_h()
navpd::back_h()
navpd::level_add($persistdata=array(), $level=0)
navpd::level_remove($persistdata, $level=0)
navpd::prepare_args($args=array())
navpd::merge_args($array1, $array2)
navpd::args_querystring($args)

*/

class navpd {

	static public function link($dest_args=array()) {
		global $self;

		$link = $self . self::args_querystring($dest_args);

		return $link;

	}

	static public function self($self_args=array()) {
		global $self;

		//$args = self::prepare_args($persistdata);
		$args = self::merge_args($_GET, $self_args);

		$selflink = $self . self::args_querystring($args);

		return $selflink;

	}

	static public function forward($dest_args=array(), $curr_args=array()) {
		global $self;

		//$args = self::prepare_args($curr_args);
		$args = self::merge_args($_GET, $curr_args);

		$args_pd = self::level_add($args);
		$persistdata = self::merge_args($args_pd, $dest_args);
		$link = $self . self::args_querystring($persistdata);

		return $link;

	}

	static public function back($dest_args=array()) {
		global $self;

		$args_pd = self::level_remove($_GET);
		$persistdata = self::merge_args($args_pd, $dest_args);
		$link = $self . self::args_querystring($persistdata);

		return $link;

	}

	static public function self_form($self_args=array()) {

		$args = self::merge_args($_GET, $self_args);

		$formfield_html = lib::array_formfield($args);

		return $formfield_html;

	}

	static public function link_h() {
		$func_get_args = func_get_args();
		return htmlentities(call_user_func_array(array('self', 'link'), $func_get_args));
	}

	static public function self_h() {
		$func_get_args = func_get_args();
		return htmlentities(call_user_func_array(array('self', 'self'), $func_get_args));
	}

	static public function forward_h() {
		$func_get_args = func_get_args();
		return htmlentities(call_user_func_array(array('self', 'forward'), $func_get_args));
	}

	static public function back_h() {
		$func_get_args = func_get_args();
		return htmlentities(call_user_func_array(array('self', 'back'), $func_get_args));
	}

	static public function level_add($persistdata=array(), $level=0) {

		if ($level > 10) {
			throw new Exception('Too much recursion');
		}

		$args = array();
		foreach ($persistdata as $name => $value) {
			if (is_array($value)) {
				$args['pd_' . $name] = self::level_add($value, $level + 1);
			} else {
				$args['pd_' . $name] = $value;
			}

		}

		return $args;

	}

	static public function level_remove($persistdata, $level=0) {

		if ($level > 10) {
			throw new Exception('Too much recursion');
		}

		$args = array();
		foreach ($persistdata as $name => $value) {

			if (preg_match("/^pd_/", $name)) {

				$namewopd = preg_replace('/^pd_/', '', $name);

				if (is_array($value)) {
					$args[$namewopd] = self::level_remove($value, $level + 1);
				} else {
					$args[$namewopd] = $value;
				}

			}

		}

		return $args;

	}

/*
	static public function prepare_args($args=array()) {
		$prepargs = self::merge_args($_GET, $args);
		return $prepargs;
	}
*/

	static public function merge_args($array1, $array2) {

		$mergeargs = array();

		foreach ($array1 as $name => $value) {
			if (!array_key_exists($name, $array2)) {
				$mergeargs[$name] = $value;
			}
		}

		foreach ($array2 as $name => $value) {
			if ($value !== null) {
				$mergeargs[$name] = $value;
			}
		}

		return $mergeargs;

	}

	static public function args_querystring($args) {

		$querystring = http_build_query($args);
		$querystring = ($querystring) ? '?' . $querystring : '';

		return $querystring;

	}

}

?>