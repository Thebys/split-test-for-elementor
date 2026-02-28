<?php

namespace SplitTestForElementor\Classes\Misc;

class Util {

	public static function generateV4UUID() {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),

			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,

			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	public static function setCookie($name, $value, $time = '+12 month') {
		$path = parse_url(home_url('/'), PHP_URL_PATH);
		$host = parse_url(home_url('/'), PHP_URL_HOST);
		$expiry = strtotime($time);
		setcookie($name, $value, $expiry, $path, $host);
		$_COOKIE[$name] = $value;
	}

	public static function validInt($value) {
		$number = filter_var(ltrim($value, '0'), FILTER_VALIDATE_INT);
		return ($number !== FALSE);
	}

	public static function notSetOrNullOrEmpty($array, $index) {
		return isset($array[$index]) ? self::nullOrEmpty($array[$index]) : false;
	}

	public static function nullOrEmpty($value) {
		return $value == null || $value == "null" || $value == "";
	}

	public static function urlExists($url) {
		if (($url == '') || ($url == null)) { return false; }
		$response = wp_remote_head( $url, array( 'timeout' => 5 ) );
		$accepted_status_codes = array(200);
		if ( ! is_wp_error( $response ) && in_array( wp_remote_retrieve_response_code( $response ), $accepted_status_codes ) ) {
			return true;
		}
		return false;
	}

}