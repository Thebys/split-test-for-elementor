<?php

namespace SplitTestForElementor\Admin\Classes\Misc;

class ColorUtil {

	private static $colors = [
		['r' => '128',  'g' => '128',   'b' => '128'],
		['r' => '63',   'g' => '175',   'b' => '108'],
		['r' => '237',  'g' => '103',   'b' => '122'],
		['r' => '78',   'g' => '188',   'b' => '237'],
		['r' => '225',  'g' => '90',    'b' => '237'],
		['r' => '90',   'g' => '115',   'b' => '237'],
		['r' => '255',  'g' => '224',   'b' => '79'],
		['r' => '90',   'g' => '237',   'b' => '202'],
		['r' => '237',  'g' => '144',   'b' => '114'],
	];

	public static function getColor($number, $opacity = 1) {
		if (isset(self::$colors[$number])) {
			$color = self::$colors[$number];
		} else {
			$color = self::$colors[0];
		}
		return "rgba(".$color['r'].", ".$color['g'].", ".$color['b'].", ".$opacity.")";
	}

}