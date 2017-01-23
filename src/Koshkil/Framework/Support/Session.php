<?php
namespace Koshkil\Framework\Support;

class Session {

	public static function start() {
		session_start();
	}

	public static function set($var,$val) {
		$_SESSION[$var]=$val;
	}

	public static function get($var) {
		return $_SESSION[$var];
	}

}