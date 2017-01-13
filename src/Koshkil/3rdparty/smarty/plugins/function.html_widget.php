<?php
use Koshkil\Framework\Core\Web\Support\Request;

function smarty_function_html_widget($params, &$template) {
	$wgType=$params["name"];
	$widgetPath=explode(".",$wgType);

	if (count($widgetPath)==1)
		$widgetPath=$className=ucwords(strtolower($wgType))."Widget";
	else {
		$className=ucwords(strtolower(array_pop($widgetPath)))."Widget";
		$widgetPath=implode("\\",$widgetPath).".".$className;
	}
	$namespaces=[
		"app\\Widgets"=>"app/Widgets",
		"Koshkil\\Framework\\Core\\Web\\Widgets"=>"vendor/koshkil/framework/src/Koshkil/Framework/Core/Web/Widgets",
	];
	//echo
	$widget=null;
	foreach($namespaces as $namespace => $path) {
		$_className=$namespace."\\".$className;
		if (!file_exists(Application::get("PHYS_PATH")."/{$path}/{$className}.php"))
			continue;

		echo Application::get("PHYS_PATH")."/{$path}/{$className}.php<br/>";
		require_once(Application::get("PHYS_PATH")."/{$path}/{$className}.php");
		try {
			$widget=new $_className();
		} catch (\Exception $e) {}
		if (is_object($widget)) break;
	}
	if (is_object($widget)) {
		$request=new Request($params);
		return $widget->run($request);
	} else
		return "<b>Widget not found</b>";
}