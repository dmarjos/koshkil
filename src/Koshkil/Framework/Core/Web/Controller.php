<?php
namespace Koshkil\Framework\Core\Web;

use Koshkil\Framework\Core\Web\Support\Request;
use Koshkil\Framework\Core\Exceptions\TemplateNotFound;
use Koshkil\Framework\Core\Application;

abstract class Controller {

	protected $lifeCycle=["create","init","run","output"];
	public $view=null;
	public $templateFile="";

	public function __construct() {

		if (Application::get("DISABLE_CACHE")) {
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		}

		$this->view=new \Smarty();

		$this->view->addPluginsDir(Application::get("VENDOR_DIR")."/3rdparty/smarty/plugins");

		$this->templateFile="main.tpl";
		//dump_var($this->view);
		$templatesDir=Application::get("TEMPLATES_DIR");
		$storageDir=Application::get("STORAGE_DIR");

		$this->view->compile_dir=$storageDir."/framework/views";
		$this->view->setTemplateDir($templatesDir);

    	$this->view->assign("template","empty.tpl");
	}

	protected function create(Request $request) {
		return true;
	}

	protected function init(Request $request) {
		return true;
	}

	protected function run(Request $request) {
		return true;
	}

	public function isLoggedIn() {
		return false;
	}

	protected function output(Request $request) {
        $this->view->assign("scripts",Application::get("scripts"));
        $this->view->assign("bottom_scripts",Application::get("bottom_scripts"));
        $this->view->assign("styles",Application::get("styles"));
        $this->view->assign("metas",Application::get("metatags"));

       	$this->view->assign("template",$this->getTemplateName());
        $this->view->display($this->templateFile);
		return true;
	}

	protected function getTemplateName() {
		$template=$this->view->tpl_vars["template"]->value;
		if ($template=="empty.tpl") {
			$requestedUri=str_replace($scriptName,"",$_SERVER["REQUEST_URI"]);
			list($uri,$qs)=@explode("?",$requestedUri);
			if (substr($uri,0,strlen(Application::get("BASE_DIR")))==Application::get("BASE_DIR"))
				$uri=substr($uri,strlen(Application::get("BASE_DIR")));

			$sep=(substr($uri,0,1)!="/"?"/":"");
			$uri=strtolower(dirname($uri)."/".basename($uri,".php"));
			$uri=str_replace("//","/",$uri);
			$uri=str_replace("\\/","/",$uri);
			if (substr($uri,0,10)=="/index.php") $uri=substr($uri,10);
			if (substr($uri,0,1)=="/") $uri=substr($uri,1);
			if (empty($uri)) $uri="index";
			//$uri=str_replace("-","_",$uri);
			$template=$uri.".tpl";

			$templatesDir=Application::get("TEMPLATES_DIR");

			if (!file_exists($templatesDir."/{$template}")) {
				dump_var("{$templatesDir}/{$template}");
				echo "<!-- {$templatesDir}/{$template} -->";
				$template="404.tpl";
			}
			if (!file_exists($templatesDir."/{$template}")) {
				throw new TemplateNotFound($templatesBaseDir."/{$template} noexiste!");
				$template="empty.tpl";
			} else {
				$template=$templatesDir."/{$template}";
			}
		}

		return $template;
	}


	public function execute() {
		$request=new Request();
		foreach($this->lifeCycle as $method) {
			if(call_user_func([$this,$method],$request)===false)
				break;
		}
	}

}