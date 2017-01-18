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

function __autoload($className) {
echo "wtf?!?!?!?!?!?<br/>";
	dump_var($className);
}

function get($var,$src=null) {
	return Application::get($var,$src);
}

function getLink($path) {
	return Application::getLink($path);
}

function asset($path) {
	return Application::getAsset($path);
}

