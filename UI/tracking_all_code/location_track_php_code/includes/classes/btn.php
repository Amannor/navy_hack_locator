<?php

/*
$cfg['btn_template_path'] = 'btn_template/';
$cfg['btn_cache_path'] = 'btn_cache/';

echo btn::create('Edit', btn::TYPE_LINK, 'page.php?id=2', 'graphic.gif', "alert('123')", 'alttext', 'titletext');
echo btn::create('Edit', btn::TYPE_LINK, 'page.php?id=2', 'graphic.gif', "alert('123')", 'alttext', 'titletext');
echo btn::create_nolink('Edit', 'graphic.gif', 'alttext', 'titletext');
echo btn::create('Update', btn::TYPE_SUBMIT);
*/

class btn {

	const TYPE_LINK = 1;
	const TYPE_SUBMIT = 2;

	const CREATE_CACHEIMG = true;

	//Standard button
	static public function create($text, $type, $link='', $image='', $onclick='', $alttext='', $title='') {
		global $cfg;

		$imagefilename = md5($text . $image . 'create');

		$btn_imagefile = $cfg['btn_cache_path'] . $imagefilename . '.gif';

		if ( (self::CREATE_CACHEIMG == false) || (file_exists($btn_imagefile) == false) ) {

			$btn_image = new btn_image();
			$btn_image->settemplate($cfg['btn_template_path'] . 'template_left.gif', $cfg['btn_template_path'] . 'template_middle.gif', $cfg['btn_template_path'] . 'template_right.gif');
			$btn_image->setpadding(5);
			$btn_image->setbtngraphic($image);
			$btn_image->settext($text, 8, '#000000', $cfg['btn_font_path']); //#91aa9d
			$btn_image->create();
			$btn_image->save($btn_imagefile);
			$btn_image->finished();

		}

		if ($type == self::TYPE_LINK) {

			if ($onclick) {
				$onclick_html = ' onclick="' . htmlentities($onclick) . '"';
			} else {
				$onclick_html = '';
			}

			if ($alttext) {
				$alttext_value = $alttext;
			} else {
				$alttext_value = $text;
			}

			if ($title) {
				$title_html = ' title="' . htmlentities($title) . '"';
			} else {
				$title_html = '';
			}

			$link_h = htmlentities($link);
			$alttext_value_h = htmlentities($alttext_value);

			$btn_html = <<<EOHTML
<a href="{$link_h}"{$onclick_html}><img src="{$cfg['site_url']}{$btn_imagefile}" alt="{$alttext_value_h}"{$title_html} /></a>
EOHTML;

		} else if ($type == self::TYPE_SUBMIT) {

			$text_h = htmlentities($text);

			$btn_html = <<<EOHTML
<input type="image" src="{$cfg['site_url']}{$btn_imagefile}" value="{$text_h}" name="submit" />
EOHTML;

		} else {
			throw new Exception("Type \"{$type}\" invalid");
		}

		return $btn_html;

	}

	//Button without link (unclickable)
	static public function create_nolink($text, $image, $alttext='', $title='') {
		global $cfg;

		$imagefilename = md5($text . $image . 'create_nolink');

		$btn_imagefile = $cfg['btn_cache_path'] . $imagefilename . '.gif';

		if ( (self::CREATE_CACHEIMG == false) || (file_exists($btn_imagefile) == false) ) {

			$btn_image = new btn_image();
			$btn_image->settemplate($cfg['btn_template_path'] . 'template_left.gif', $cfg['btn_template_path'] . 'template_middle.gif', $cfg['btn_template_path'] . 'template_right.gif');
			$btn_image->setpadding(5);
			$btn_image->setbtngraphic($image);
			$btn_image->settext($text, 8, '#868686', $cfg['btn_font_path']);
			$btn_image->create();
			$btn_image->save($btn_imagefile);
			$btn_image->finished();

		}

		if ($alttext) {
			$alttext_value = $alttext;
		} else {
			$alttext_value = $text;
		}

		if ($title) {
			$title_html = ' title="' . htmlentities($title) . '"';
		} else {
			$title_html = '';
		}

		$alttext_value_h = htmlentities($alttext_value);

		$btn_html = <<<EOHTML
<img src="{$cfg['site_url']}{$btn_imagefile}" alt="{$alttext_value_h}"{$title_html} />
EOHTML;

		return $btn_html;

	}

}

?>