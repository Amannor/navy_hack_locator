<?php

/*
$btn_image = new btn_image();
$btn_image->settemplate('template_left.gif', 'template_middle.gif', 'template_right.gif');
$btn_image->setpadding(5);
$btn_image->setbtngraphic('graphic.gif');
$btn_image->settext("Update", 5, '#000000');
$btn_image->create();
$btn_image->save();
$btn_image->finished();
*/

//http://www.webdesignerstoolkit.com/buttons.php#download

class btn_image {

	private $templatefile_left;
	private $templatefile_middle;
	private $templatefile_right;

	private $text;
	private $textsize;
	private $textfontfile;
	private $textcolor;
	private $btngraphic;
	private $padding;
	private $btn_img;

	public function settemplate($filename_left, $filename_middle, $filename_right) {
		$this->templatefile_left = $filename_left;
		$this->templatefile_middle = $filename_middle;
		$this->templatefile_right = $filename_right;
	}

	public function setpadding($padding) {
		$this->padding = $padding;
	}

	public function settext($text, $textsize, $textcolor, $textfontfile='') {
		$this->text = $text;
		$this->textsize = $textsize;
		$this->textcolor = $textcolor;
		$this->textfontfile = $textfontfile;
	}

	public function setbtngraphic($btngraphic) {
		$this->btngraphic = $btngraphic;
	}

	public function create() {

		if ( (!$this->text) && (!$this->btngraphic) ) {
			throw new Exception('Text of graphic must be set');
		}

		$template_left_img = img::retrieve_fromfile($this->templatefile_left, img::type_fromfilename($this->templatefile_left));
		$template_left_dimen = img::dimensions($template_left_img);

		$template_middle_img = img::retrieve_fromfile($this->templatefile_middle, img::type_fromfilename($this->templatefile_middle));
		$template_middle_dimen = img::dimensions($template_middle_img);

		$template_right_img = img::retrieve_fromfile($this->templatefile_right, img::type_fromfilename($this->templatefile_right));
		$template_right_dimen = img::dimensions($template_right_img);

		if ($this->btngraphic) {
			$btngraphic_img = img::retrieve_fromfile($this->btngraphic, img::type_fromfilename($this->btngraphic));
			$btngraphic_dimen = img::dimensions($btngraphic_img);
		}

		if ( ($template_left_dimen['height'] != $template_middle_dimen['height']) || ($template_left_dimen['height'] != $template_right_dimen['height']) ) {
			throw new Exception('Template image heights must match');
		}

		if ($this->btngraphic) {
			if ($btngraphic_dimen['height'] > $template_left_dimen['height']) {
				throw new Exception('Button graphic bigger than button template');
			}
		}

		if ($this->text) {
			$textsizeinfo = imagettfbbox($this->textsize, 0, $this->textfontfile, $this->text);
			$text_width = ($textsizeinfo[2] - $textsizeinfo[0]) + $this->padding;
			$text_height = ($textsizeinfo[7] - $textsizeinfo[1]);// + $this->padding;

			//$text_width = imagefontwidth($this->textsize) * strlen($this->text) + $this->padding;
			//$text_height = imagefontheight($this->textsize);

		} else {
			$text_width = 0;
			$text_height = 0;
		}

		if (!$this->text) {
			$this->padding = $this->padding * 1.1;
		}

		if ($text_height > $template_left_dimen['height']) {
			throw new Exception('Text with set font bigger than button template');
		}

		$btn_height = $template_left_dimen['height'];

		$btn_width = $text_width + $this->padding;

		if ($this->btngraphic) {
			$btn_width += $this->padding + $btngraphic_dimen['width'];
		}

		$btn_img = imagecreatetruecolor($btn_width, $btn_height);

		if ($btn_img === false) {
			throw new Exception('Unable to create destination image');
		}

		$color = imagecolorallocate($btn_img, 0, 255, 0);
		imagefill($btn_img, 0, 0, $color);
		imagecolortransparent($btn_img, $color);

		$currwidth_pos = 0;

		imagecopymerge($btn_img, $template_left_img, 0, 0, 0, 0, $template_left_dimen['width'], $btn_height, 100);

		$currwidth_pos += $template_left_dimen['width'];

		while ($currwidth_pos < ($btn_width - $template_right_dimen['width'])) {
			imagecopymerge($btn_img, $template_middle_img, $currwidth_pos, 0, 0, 0, 1, $btn_height, 100);
			$currwidth_pos++;
		}

		imagecopymerge($btn_img, $template_right_img, $currwidth_pos, 0, 0, 0, $template_right_dimen['width'], $btn_height, 100);

		$textstart_posx = $this->padding;

		if ($this->btngraphic) {

			$btngraphic_posy = ($btn_height - $btngraphic_dimen['height']) / 2;
			imagecopymerge($btn_img, $btngraphic_img, $this->padding, $btngraphic_posy, 0, 0, $btngraphic_dimen['width'], $btngraphic_dimen['height'], 100);

			$textstart_posx += $btngraphic_dimen['width'] + $this->padding;
		}

		if ($this->text) {

			$colorarr = self::hextorgb_color($this->textcolor);

			$color = imagecolorallocate($btn_img, $colorarr['red'], $colorarr['green'], $colorarr['blue']);

			$textstart_posy = ($btn_height - $text_height) / 2;

			imagettftext($btn_img, $this->textsize, 0, $textstart_posx, $textstart_posy, $color, $this->textfontfile, $this->text);
			//imagestring($btn_img, $this->textsize, $textstart_posx, $textstart_posy, $this->text, $color);

		}

		$this->btn_img = $btn_img;

		img::destroy($template_left_img);
		img::destroy($template_middle_img);
		img::destroy($template_right_img);

		if ($this->btngraphic) {
			img::destroy($btngraphic_img);
		}

	}

	public function save($filename) {
		img::save_tofile($this->btn_img, IMAGETYPE_GIF, $filename);
	}

	public function finished() {
		img::destroy($this->btn_img);
	}

	//Convert html color to rgb color
	private function hextorgb_color($color) {

		if (!preg_match("/^\#[a-f0-9]{6}$/", $color)) {
			throw new Exception('Color not valid');
		}

		/*
		$arrtmp = explode(' ', chunk_split($color, 2, " "));
		$arrtmp = array_map("hexdec", $arrtmp);
		return array('red' => $arrtmp[0]/255, 'green' => 
		*/

		return array('red' => hexdec('0x' . $color{1} . $color{2}), 'green' => hexdec('0x' . $color{3} . $color{4}), 'blue' => hexdec('0x' . $color{5} . $color{6}));

	}

}

?>