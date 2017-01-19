<?php
namespace Koshkil\Framework\Core;

use Koshkil\Framework\Routing\RewriteManager;
use Koshkil\Framework\Routing\RoutesManager;
use Koshkil\Framework\Core\Web\Controller;
use Koshkil\Framework\Core\Exceptions\ControllerNotFound;
use Koshkil\Framework\Core\Application;

class koshkil {

	public function run() {
		RewriteManager::processRules();
		RoutesManager::processRoutes();

		$scriptName=$_SERVER["SCRIPT_NAME"];

		if ($_SERVER["REDIRECT_URL"] && $_SERVER["REDIRECT_URL"]!="/index.php")
			$requestedUri=$_SERVER["REDIRECT_URL"].($_SERVER["REDIRECT_QUERY_STRING"]?"?".$_SERVER["REDIRECT_QUERY_STRING"]:"");
		else
			$requestedUri=str_replace($scriptName,"",$_SERVER["REQUEST_URI"]);

		list($uri,$qs)=@explode("?",$requestedUri);

		$sep=(substr($uri,0,1)!="/"?"/":"");
		$path=explode("/",dirname($uri));
		foreach($path as &$folder) {
			$folder=ucwords(strtolower($folder));
		}
		$path=implode("/",$path);
		$uri=basename($uri,".php");
		$uri=str_replace("-","_",$uri);
		if (empty($uri)) $uri="index";
		$uri=ucwords(strtolower($uri));
		$uri=$path."/".$uri."Controller";
		if (substr($uri,0,strlen(Application::get("BASE_DIR")))==Application::get("BASE_DIR"))
			$uri=substr($uri,strlen(Application::get("BASE_DIR")));

		if (substr($uri,0,2)=="//") $uri=substr($uri,1);


		$controllerNamespace="app";
		if (Application::get("APP_NAME"))
			$controllerNamespace.="/".Application::get("APP_NAME");
		$controllerNamespace.="/Controllers".dirname($uri);
		$controller=Application::get("APPLICATION_DIR")."/Controllers".$uri.".php";
		Application::set("RUNNING_CONTROLLER",$uri);

		if (!file_exists($controller)) {
			$defaultController=Application::get("DEFAULT_CONTROLLER");
			$defaultController=str_replace(".php","",$defaultController);
			if (substr($defaultController,0,1)!="/") $defaultController="/".$defaultController;
			$controller=Application::get("APPLICATION_DIR")."/Controllers{$defaultController}.php";
			if (!file_exists($controller)) {
				throw new ControllerNotFound("Controller not found (".Application::get("RUNNING_CONTROLLER").") and not default controller found");
			}
		}

		$className=basename($controller,".php");
		$controllerNamespace.=(substr($controllerNamespace,-1)!="/"?"/":"").$className;
		$controllerNamespace=implode("\\",explode("/",$controllerNamespace));

		require_once($controller);

		if (!class_exists($controllerNamespace))
			die("Class {$controllerNamespace} not defined on {$controller}");

		$page=new $controllerNamespace();
		if (!$page instanceof Controller)
			die("Class {$className} must be an instance of Koshkil\Framework\Core\Web\Controller or a descendant");

		Application::$page=&$page;

		try {
			$page->execute();
		} catch (Exception $e) {
			dump_var($e);
		}

		//Application::dumpConfig();
	}

	public function done() {
	}
}