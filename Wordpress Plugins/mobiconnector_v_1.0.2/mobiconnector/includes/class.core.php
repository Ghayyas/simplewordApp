<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class MobiConnectorCore {
	public static function resize_image($file, $dest, $w, $h, $crop=false) {
		list($width, $height) = getimagesize($file);
		$r = $width / $height;
		$w_rate = $width/$w;
		$h_rate = $height/$h;
		/*$r2 = $w/$width;
		$r3 = $h/$height;
		$scale = $r2>$r3?$r2:$r3;
		if($scale > 1 && $scale == $r2){
			$width *=$scale ;
		}
		if($scale > 1 && $scale == $r3){
			$height *=$scale ;
		}
		if ($crop) {
			if ($width > $height) {
				$width = ceil($width-($width*abs($r-$w/$h)));
			} else {
				$height = ceil($height-($height*abs($r-$w/$h)));
			}
			$newwidth = $w;
			$newheight = $h;
		} else {
			if ($w/$h > $r) {
				$newwidth = $h*$r;
				$newheight = $h;
			} else {
				$newheight = $w/$r;
				$newwidth = $w;
			}
		}
		*/
		$newwidth = $w;
		$newheight = $h;
		if ($crop) {
			if ($width > $height) {
				$width = ceil($width-($width*abs($r-$w/$h)));
			} else {
				$height = ceil($height-($height*abs($r-$w/$h)));
			}
			$newwidth = $w;
			$newheight = $h;
		} else {
			if($w_rate >= 1 && $h_rate>=1) {
				$newwidth = $w;
				$newheight = $h;
			}else if($w_rate >= 1 && $h_rate<1) {
				$newwidth = $width*(1/$h_rate);
				$newheight = $h;
			}else if($w_rate < 1 && $h_rate>=1) {
				$newwidth = $w;
				$newheight = $height*(1/$w_rate);
				
			}else if($w_rate <= 1 && $h_rate<=1){
				if($w_rate>=$h_rate) {
					$newwidth = $width*(1/$h_rate);
					$newheight = $h;
				}else{
					$newwidth = $w;
					$newheight = $height*(1/$w_rate);
				}
			}
		}
		// end scale image
		$newwidth = ceil($newwidth);
		$newheight = ceil($newheight);
		
		
		$filetype = wp_check_filetype( basename( $file), null );
		$filetype['ext'] = strtolower($filetype['ext']);
		if($filetype['ext'] =='jpg' || $filetype['ext'] =='jpeg')
			$source = imagecreatefromjpeg($file);
		else
			$source = imagecreatefrompng($file);
		
		/* scale image **/
		$thumb_tmp = imagecreatetruecolor($newwidth, $newheight);
		$black = imagecolorallocate($thumb_tmp, 0, 0, 0);
		// Make the background transparent
		imagecolortransparent($thumb_tmp, $black);
		imagecopyresampled($thumb_tmp, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		/*** crop scale image **/
		//var_dump($newwidth);var_dump($newheight);die;
		$thumb = imagecreatetruecolor($w, $h);
		$black = imagecolorallocate($thumb, 0, 0, 0);
		@imagecolortransparent($thumb, $black);
		@imagecopyresampled($thumb, $thumb_tmp, 0, 0, 0, 0, $w, $h, $w, $h);
		// remove OLD image
		@unlink($dest);
		// Output
		if($filetype['ext'] =='jpg' || $filetype['ext'] =='jpeg')
			@imagejpeg($thumb, $dest, 100);
		else
			@imagepng($thumb, $dest, 9);
		
		@imagedestroy($thumb);
	}
}