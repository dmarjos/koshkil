<?php
namespace Koshkil\Framework\Support\Session;

use Koshkil\Framework\Support\Session;

class FlashMessages extends Session {
	const FLASH_TYPE_ERROR = 1;
	const FLASH_TYPE_SUCCESS = 2;

	public static function clearMessages() {
		self::set("error_message",null);
		self::set("success_message",null);
	}

	public static function setMessage($message,$type=1) {

		$varNames=array(1=>"error",2=>"success");

		self::set($varNames[$type]."_message",$message);
	}

}