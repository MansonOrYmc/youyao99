<?php
apf_require_class('APF_Controller');

class User_CaptchaController extends APF_Controller {
	public function handle_request(){
		$this->create_img();
	}

	private function create_img() {
		$randval = $this->rand_str();
		$time = time();
		$randval_lowr = strtolower($randval);
		$code_md5 = md5($randval_lowr . 'yya');
		$_SESSION['captval'] = $time.','.$code_md5;
		$im = imagecreate(129, 42);
		imagecolorallocate($im, 255, 255, 255);
		putenv('GDFONTPATH=' . realpath('.'));
		$font = APP_PATH."font/segoepr.ttf";
                #Logger::info(__FILE__, __CLASS__, __LINE__, "font: $font");
		#$font = "/data/webapp/PROD/app-youyao-core/2017_06_01/classes/includes/font/segoepr.ttf";
                #Logger::info(__FILE__, __CLASS__, __LINE__, "font: $font");
		for ($i = 0; $i < 5; $i++) {
			$array = array(-1, 0, 1);
			$p = array_rand($array);
			$an = $array[$p] * mt_rand(1, 15);
			$x = 15;
			$ran_color = $this->rand_color();
			$textColor = imagecolorallocate($im, $ran_color[0], $ran_color[1], $ran_color[2]);
			imagettftext($im, 20, $an, $x + 20 * $i, 30, $textColor, $font, substr($randval, $i, 1));
			imagettftext($im, 20, $an, $x + 20 * $i + 1, 30, $textColor, $font, substr($randval, $i, 1));
		}
                Logger::info(__FILE__, __CLASS__, __LINE__, "str: $randval_lowr");
                Logger::info(__FILE__, __CLASS__, __LINE__, var_export($_SESSION, true));
		$lineColor = imagecolorallocate($im, mt_rand(100, 200), mt_rand(50, 250), mt_rand(150, 200));
		$a = mt_rand(7, 13);
		$b = mt_rand(-1, 1);
		$h = mt_rand(15, 25);
		$s = mt_rand(0, 10);
		$w = mt_rand(8, 12);
		for ($l = -3.5; $l < 3.5; $l = $l + 0.01) {
			if ($b == "0") {
				$b = "1";
			}
			$y = $b * $a * sin($l * $w / 10) + $h;
//			imagesetpixel($im, 17 * ($l + 3.5) + $s, $y + 1, $lineColor);
			imagesetpixel($im, 17 * ($l + 3.5) + $s, $y, $lineColor);
		}
		header("Content-type:image/png");
		imagepng($im);
		imagedestroy($im);
	}


	private function rand_str() {
		$chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz2346789';
		$char = str_shuffle($chars);
		$str = substr($char, 0, 5);

		return $str;
	}

	private function rand_color() {
		$color1 = array('63', '125', '74');
		$color2 = array('108', '136', '210');
		$color3 = array('243', '49', '234');
		$color4 = array('129', '122', '176');
		$color5 = array('232', '117', '54');
		$color6 = array('169', '147', '240');
		$color7 = array('53', '128', '186');
		$color8 = array('16', '191', '168');
		$color9 = array('199', '226', '140');
		$color10 = array('204', '153', '55');
		$color = array(
			$color1,
			$color2,
			$color3,
			$color4,
			$color5,
			$color6,
			$color7,
			$color8,
			$color9,
			$color10
		);
		$color_rand = ($color[array_rand($color, 1)]);
		return $color_rand;
	}
}
