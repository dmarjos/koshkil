<?php
namespace Koshkil\Framework\Core\Traits\ModelDriven;

use Koshkil\Framework\Core\Application;

trait HandlesModels {

	protected function loadModel($modelName) {
		$modelPath=explode("\\",$modelName);
		$rootNamespace=array_shift($modelPath);
		if ($rootNamespace=="App") $rootNamespace="app";
		array_unshift($modelPath, $rootNamespace);
		$modelPath=implode("/",$modelPath);
		if (file_exists(Application::get("PHYS_PATH")."/".$modelPath.".php"))
			require_once(Application::get("PHYS_PATH")."/".$modelPath.".php");
		else if(file_exists(dirname(Application::get('VENDOR_DIR'))."/".$modelPath.".php"))
			require_once(dirname(Application::get('VENDOR_DIR'))."/".$modelPath.".php");
		else
			throw new ModelNotFound("Model ".self::$userModel." wasn't found");

//		return new $modelName();

	}
}