<?php
use Koshkil\Framework\Core\Application;
if (!function_exists("dump_var")) {
	function dump_var($var,$die=true) {
		$debug=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
		echo "<pre>";
		if (is_object($var)) {
			$methods=get_class_methods($var);
			if ($methods) {
				var_dump($methods);
				echo "</pre><hr/><pre>";
			}
		}
		var_dump($var);
		if ($die) die();
	}
}

function app() {
	$method=null;
	$parameters=func_get_args();
	if(count($parameters)>0)
		$method=array_shift($parameters);
	if (!is_null($method) && method_exists(Application::$page,$method)) {
		return call_user_func_array([Application::$page,$method], $parameters);
	} else
		return Application::$page;
}
function get($var,$src=null) {
	return Application::get($var,$src);
}

function getLink($path) {
	return Application::getLink($path);
}

function asset($path,$withinTheme=false,$theme=null) {
	return Application::getAsset($path,$withinTheme,$theme);
}

