<?php
class Application {
	private static $config = array();

	public static function set($var,$val) {
		if (!is_null($val)) {
			if ($var!="page")
				self::$config[$var]=$val;
			else
				self::$page = &$val;
		} else {
			if ($var!="page")
				unset(self::$config[$var]);
		}
	}

	public static function get($var, $src = null) {
		if (is_null($src))
			$src = self::$config;
		if ($var != "page")
			return (isset($src[$var]) ? $src[$var] : null);
		else
			return self::$page;
	}

	public static function loadConfig($configFiles=false,$debug=false) {
		$physicalFolder=dirname($_SERVER["DOCUMENT_ROOT"]);
		self::set("DEFAULT_CONTROLLER","index");
		self::set("LOG_USERS","true");
		self::set("PHYS_PATH",$physicalFolder);
		$webPath=str_replace(realpath($_SERVER["DOCUMENT_ROOT"]),'',$physicalFolder);
		self::set("WEB_PATH",$webPath);
		$configFolder=$physicalFolder."/config";

		if ($configFiles===false)
			$configFiles=array("config.php","database.php");

		if (!is_array($configFiles))
			$configFiles=array($configFiles);

		if ($debug) dump_var($configFiles);

		foreach($configFiles as $configFile) {
			if (file_exists($configFolder."/{$configFile}")) {
				self::loadConfigFile($configFolder."/{$configFile}");
			}

			if (file_exists($configFolder."/".$_SERVER["SERVER_NAME"]."/{$configFile}")) {
				self::loadConfigFile($configFolder."/".$_SERVER["SERVER_NAME"]."/{$configFile}");
			} else if (file_exists($configFolder."/".$_SERVER["SERVER_NAME"].".{$configFile}")) {
				self::loadConfigFile($configFolder."/".$_SERVER["SERVER_NAME"].".{$configFile}");
			} else if (file_exists($configFolder."/".$_SERVER["SERVER_NAME"].".php") && $configFile=="config.php") {
				self::loadConfigFile($configFolder."/".$_SERVER["SERVER_NAME"].".php");
			}
		}
	}

	public static function dumpConfig($die = true) {
		dump_var(array(self::$page, self::$config), $die);
	}

}
