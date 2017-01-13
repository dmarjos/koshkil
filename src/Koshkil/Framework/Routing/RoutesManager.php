<?php
namespace Koshkil\Framework\Routing;

class RoutesManager {
	private static $routes=[];
	private static $groups=[];

	public static function dumpRoutes() {
		dump_var([self::$routes,self::$groups]);
	}

	public static function buildRoute($controller) {
		$retVal=[];
		$reflection = new \ReflectionFunction($controller);
		if ($reflection->isClosure()) {
			$retVal["call"]=$controller;
		} /*else if (is_array($controller)) {
			foreach($controller as $key => $value) {
				if (in_array($key,["as","uses"]))
					$retVal[$key]=$value;
			}
		} */ else if (is_string($controller)) {
			$retVal["controller"]=$controller;
		}
		return $retVal;
	}
	public static function get($url, $controller) {
		$route=["method"=>"GET"];
		$parameters=self::buildRoute($controller);
		self::$routes[$url]=["method"=>"GET","controller"=>$controller];
	}

	public static function group(array $grouping) {
		$prefix="";
		$folder="";

		if(isset($grouping["prefix"]))
			$prefix=$grouping["prefix"];

		if(isset($grouping["folder"]))
			$folder=$grouping["folder"];

		if (empty($prefix) || empty($folder)) return;

		self::$groups[$prefix]=$folder;
	}

	public static function processRoutes() {
		$origUri=$uri=$_SERVER["REDIRECT_URL"];
		if (!$uri || $uri=="/index.php") $uri=$_SERVER["REQUEST_URI"];

		if (substr($uri,0,strlen(Application::get('BASE_DIR')))==Application::get('BASE_DIR'))
			$uri=substr($uri,strlen(Application::get('BASE_DIR')));

		if (substr($uri,0,1)=="/") $uri=substr($uri,1);
		if (substr($uri,0,1)!="/") $uri="/{$uri}";
		$origUri=$uri;
		foreach(self::$groups as $prefix=>$folder) {
			if (substr($uri,0,strlen($prefix))==$prefix && $uri!=$prefix) {
				$uri=strtolower($folder).substr($uri,strlen($prefix));
				if (substr($uri,0,1)!="/") $uri="/{$uri}";
				$_SERVER["REDIRECT_URL"]=$uri;
				break;
			}
		}
	}

}