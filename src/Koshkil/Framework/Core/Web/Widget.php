<?php
namespace Koshkil\Framework\Core\Web;

use Koshkil\Framework\Core\Web\Support\Request;
use Koshkil\Framework\Core\Application;

class Widget extends Controller {

	protected $name="";
	protected $parameters=[];

	public function __construct() {
		parent::__construct();

	}

	public function run(Request $request) {
		$wgParameters=Application::getWidgetParameters($request["id"]);
		$this->name=basename(get_class($this));
		$this->parameters=$wgParameters;
		$this->init($request);
		return $this->output($request);
	}

	protected function output(Request $request) {
		$templateName=implode("/",explode("\\",strtolower(get_class($this))));
		$namespaces=[
			"app/".Application::get("APP_NAME")."/widgets"=>Application::get("TEMPLATES_DIR")."/widgets",
			"app/widgets"=>Application::get("TEMPLATES_DIR")."/widgets",
			"koshkil/framework/core/web/widgets"=>Application::get("VENDOR_DIR")."/views/widgets",
		];
		foreach($namespaces as $namespace=>$templatesDir) {
			if (substr($templateName,0,strlen($namespace)+1) == $namespace."/") {
				$templateName=substr($templateName,strlen($namespace)+1);
				$this->view->setTemplateDir($templatesDir);
				break;
			}
		}
		if (substr($templateName,-6)=="widget") $templateName=substr($templateName,0,-6);
		return $this->view->fetch("{$templateName}.tpl");
	}

}