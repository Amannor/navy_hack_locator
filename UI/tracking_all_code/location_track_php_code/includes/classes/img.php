<?php

//error_reporting(E_ALL);

/*

include "lib.php";

$src_img = img::retrieve_fromfile('flower3.jpg', IMAGETYPE_JPEG);

$dst_width = 1000;
$dst_height = 0;

$src_dimen = img::dimensions($src_img);

$newdst_dimen = img::resize_sizes($src_dimen['width'], $src_dimen['height'], $dst_width, $dst_height);
$dst_img = img::resize($src_img, $src_dimen['width'], $src_dimen['height'], $newdst_dimen['width'], $newdst_dimen['height']);
img::destroy($src_img);

img::save_tofile($dst_img, IMAGETYPE_JPEG, 'flower5.jpg', 75);
img::destroy($dst_img);

lib::prh(getimagesize('flower5.jpg'));


lib::prh($newdst_dimen);

//var_dump(img::chkvalid_mimetype('image/jpeg'));
//var_dump(img::chksupp_size('flower3.jpg'));
//var_dump(img::chkvalid_extension("gif"));
//var_dump(img::chksupp_type(IMAGETYPE_GIF));

*/




class img {

	static public $maximgsize = array('width' => 2000, 'height' => 2000);

	static public $imgtype = array(

		IMAGETYPE_GIF => array(
			'name' => 'GIF',
			'mime_type' => 'image/gif',
			'extension' => array('gif'),
			'extension_save' => 'gif',
			'fromfile' => 'imagecreatefromgif',
			//'tofile' => 'imagegif',
			'supp_type' => IMG_GIF,
		),

		IMAGETYPE_JPEG => array(
			'name' => 'JPEG',
			'mime_type' => 'image/jpeg',
			'extension' => array('jpeg', 'jpg'),
			'extension_save' => 'jpg',
			'fromfile' => 'imagecreatefromjpeg',
			//'tofile' => 'imagejpeg',//quality (0-100)
			'supp_type' => IMG_JPG,
		),

		IMAGETYPE_PNG => array(
			'name' => 'PNG',
			'mime_type' => 'image/png',
			'extension' => array('png'),
			'extension_save' => 'png',
			'fromfile' => 'imagecreatefrompng',
			//'tofile' => 'imagepng',//quality (0-9), filters
			'supp_type' => IMG_PNG,
		),

	);

	static public function dimensions($im_res) {

		$dimensions = array(
			'width' => imagesx($im_res),
			'height' => imagesy($im_res),
		);

		if ( ($dimensions['width'] === false) || ($dimensions['height'] === false) ) {
			throw new Exception('Unable to retrieve dimensions');
		}

		return $dimensions;

	}

	static public function chkvalid_mimetype($mime_type) {

		foreach (self::$imgtype as $imageinfo) {
			if ($imageinfo['mime_type'] == $mime_type) {
				return true;
			}
		}

		return false;

	}

	static public function chkvalid_extension($extension) {

		foreach (self::$imgtype as $imageinfo) {
			if (in_array($extension, $imageinfo['extension'])) {
				return true;
			}
		}

		return false;

	}

	static public function chksupp_type($type) {
		if (imagetypes() & self::$imgtype[$type]) {
			return true;
		} else {
			return false;
		}
	}

	static public function chksupp_size($filename) {

		$imagesize = getimagesize($filename);

		if ($imagesize === false) {
			throw new Exception("Filename \"{$filename}\" invalid");
		}

		if ( ($imagesize[0] > self::$maximgsize['width']) || ($imagesize[1] > self::$maximgsize['height']) ) {
			return false;
		} else {
			return true;
		}

	}

	static public function mimetype_imagetype($mime_type) {

		foreach (self::$imgtype as $imagetypeno => $imageinfo) {
			if ($imageinfo['mime_type'] == $mime_type) {
				return $imagetypeno;
			}
		}

		return false;
		//throw new Exception("Mime type \"{$mime_type}\" not recognised");

	}

	static public function type_fromfilename($filename) {

		preg_match("/\.([a-zA-Z0-9]{2,5})$/", $filename, $matches);

		if (isset($matches[1])) {

			foreach (self::$imgtype as $imagetypeno => $imageinfo) {
				if (in_array($matches[1], $imageinfo['extension'])) {
					return $imagetypeno;
				}
			}

		}

		return false;
		//throw new Exception("Unable recognised type from filename \"{$filename}\"");

	}

	static public function retrieve_fromfile($filename, $type=null) {

		$resource = call_user_func(self::$imgtype[$type]['fromfile'], $filename);

		if ($resource === false) {
			throw new Exception("Unable to retrieve resource from \"{$filename}\"");
		}

		return $resource;
	}

	static public function save_tofile($im_res, $type, $filename, $quality=100, $extraoptions='') {

		switch ($type) {
			case IMAGETYPE_GIF:
				$status = call_user_func('imagegif', $im_res, $filename);
				break;
			case IMAGETYPE_JPEG:
				$status = call_user_func('imagejpeg', $im_res, $filename, $quality);
				break;
			case IMAGETYPE_PNG:
				$status = call_user_func('imagepng', $im_res, $filename, $quality, $extraoptions);
				break;
			default:
				throw new Exception("Unrecognised file type \"{$type}\"");
		}

		if ($status === false) {
			$typename = self::$imgtype[$type]['name'];
			throw new Exception("Unable to save image type \"{$typename}\" to \"{$filename}\"");
		}

	}

	static public function resize($src_img, $src_width, $src_height, $dst_width, $dst_height, $truecolor=true) {

		if ($truecolor) {
			$dst_img = imagecreatetruecolor($dst_width, $dst_height);
		} else {
			$dst_img = imagecreate($dst_width, $dst_height);
		}

		if ($dst_img === false) {
			throw new Exception('Unable to create destination image');
		}

		$status = imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $dst_width, $dst_height, $src_width, $src_height);

		if ($status === false) {
			throw new Exception('Unable to resize image');
		}

		return $dst_img;

	}

	static public function resize_sizes($src_width, $src_height, $dst_width=null, $dst_height=null) {

		if ( ($src_width <= 0) || ($src_height <= 0) ) {
			throw new Exception('Source height / width less than zero');
		}

		if ( (!$dst_width) && (!$dst_height) ) {
			throw new Exception('Destination height and/or width must be specified');
		}

		$ratio = $src_width / $src_height;

		if ($dst_width) {

			$newdst_height = $dst_width / $ratio;
			$newdst_width = $dst_width;

		} else if ($dst_height) {

			$newdst_width = $dst_height * $ratio;
			$newdst_height = $dst_height;

		} else {

			if ($dst_width / $dst_height > $ratio) {
				$newdst_width = $dst_height * $ratio;
				$newdst_height = $dst_height;
			} else {
				$newdst_height = $dst_width / $ratio;
				$newdst_width = $dst_width;
			}

		}

		$sizes = array(
			'width' => round($newdst_width),
			'height' => round($newdst_height),
		);

		return $sizes;

	}

	static public function destroy($im_res) {
		$status = imagedestroy($im_res);

		if ($status === false) {
			throw new Exception('Unable to destroy image');
		}

	}

}

?>