<?php
class Application {
	private static $config = array();
	public static $page=null;

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

		$templatesDir=Application::get("PHYS_PATH")."/resources/views";
		$storageDir=Application::get("PHYS_PATH")."/storage";
		if (Application::Get("DEFAULT_THEME")) {
			$themeTemplateDir=$templatesDir."/themes/".Application::Get("DEFAULT_THEME");
			if (file_exists($themeTemplateDir))
				$templatesDir=$themeTemplateDir;
		}

		Application::set("TEMPLATES_DIR",$templatesDir);
		Application::set("STORAGE_DIR",$storageDir);

		Application::set("VENDOR_DIR",dirname(dirname(dirname(__FILE__))));

		$urlsFolder=$physicalFolder."/app/url";
		require_once("{$urlsFolder}/rules.php");
		require_once("{$urlsFolder}/routes.php");

	}

	private static function loadConfigFile($file) {
		require_once($file);
		if (isset($CONFIG) && is_array($CONFIG)) {
			foreach ($CONFIG as $option => $value)
				self::set($option, $value);
		}
		if (isset($LABELS) && is_array($LABELS))
			self::set("CONFIG_LABELS", $LABELS);
		else
			self::set("CONFIG_LABELS", array());
	}

	public static function dumpConfig($die = true) {
		dump_var(array(self::$page, self::$config), $die);
	}

	public static function getLink($path) {
		$path_info = parse_url($path);
		if ($path_info["scheme"] && $path_info["host"])
			return $path;
		$retVal = "";
		if (substr($path, 0, 1) != "/")
			$path = "/" . $path;
		if (self::$config["MOD_REWRITE"])
			$retVal = self::get("WEB_PATH") . $path;
		else
			$retVal = self::get("WEB_PATH") . "/index.php" . $path;

		$retVal = str_replace("\\", "/", $retVal);
		$retVal = str_replace("//", "/", $retVal);
		$retVal = str_replace("/index.php//", "/index.php/", $retVal);
		$retVal = str_replace("/index.php/index.php/", "/index.php/", $retVal);

		$doubleBaseDir = Application::get("BASE_DIR") . Application::get("BASE_DIR");
		$retVal = str_replace($doubleBaseDir, Application::get("BASE_DIR"), $retVal);
		return $retVal;
	}

	public static function setWidgetParameters($name, $parameters) {
		$params = self::get("WIDGET_PARAMS");
		if (!is_array($params))
			$params = array();
		$params[$name] = $parameters;
		self::set("WIDGET_PARAMS", $params);
	}

	public static function getWidgetParameters($name) {
		$params = self::get("WIDGET_PARAMS");
		if (!is_array($params))
			$params = array();
		return $params[$name];
	}

}
