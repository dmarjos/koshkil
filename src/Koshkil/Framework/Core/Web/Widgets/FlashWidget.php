<?php
namespace Koshkil\Framework\Core\Web\Widgets;

use Koshkil\Framework\Core\Web\Widget;

class FlashWidget extends Widget {

	private $scripts;

	public function run($name="") {
		$this->name="flash";
		$this->init();
		return $this->output();
	}

	protected function init() {

		$this->scripts=Application::get("scripts");
		if (is_null($this->scripts)) $this->scripts=array();

		Application::addScript('/resources/js/plugins/noty/jquery.noty.js');
		Application::addScript('/resources/js/plugins/noty/layouts/topRight.js');
		Application::addScript('/resources/js/plugins/noty/themes/default.js');

		foreach(array("jquery.noty.js","layouts/topRight.js","themes/default.js") as $script) {
			if (!in_array("/resources/js/plugins/noty/{$script}",$this->scripts)) {
				$this->scripts[]=Application::getLink('/resources/js/plugins/noty/{$script}');
			}
		}
		$this->view->assign("type",$this->name);
	}

	/*
	protected function output() {
		return ""; //parent::output();
		if ($_SESSION["error_message"]) {
			$this->view->assign("error_message",$_SESSION["error_message"]);
			unset($_SESSION["error_message"]);
		}
	}
	*/

	public function javascript() {
		header("Content-type: text/javascript");
		$type="";
		foreach(array("error","success","warning") as $type) {
			if (isset($_SESSION["{$type}_message"])) {
				break;
			}
		}
		if (empty($_SESSION["{$type}_message"])) die();
		echo <<<END_JAVASCRIPT

$(document.body).ready(function() {
	noty({
		text: '{$_SESSION["{$type}_message"]}', type: '{$type}',"timeout":3000
	});
});
END_JAVASCRIPT;

		unset($_SESSION["error_message"]);
		die();

	}
}