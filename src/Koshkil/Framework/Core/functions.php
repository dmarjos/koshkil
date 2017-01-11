<?php
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