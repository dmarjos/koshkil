<?php
namespace Koshkil\Framework\Core\Web\Widgets;

use Koshkil\Framework\Core\Web\Widget;
use Koshkil\Framework\Core\Web\Support\Request;

class DatagridWidget extends Widget {

	protected function init(Request $request) {
		$buttons=0;
		if (($this->parameters["id_field"]&&($this->parameters["form"] || $this->parameters["custom_action"])) || ($this->parameters["indexable"])) {
			if (($this->parameters["id_field"]&&($this->parameters["form"] || $this->parameters["custom_action"])) && Application::$page->meetRules(Application::$page->rules,UserRules::UPDATE)) $buttons++;
			if (($this->parameters["id_field"]&&($this->parameters["form"] || $this->parameters["custom_action"])) && Application::$page->meetRules(Application::$page->rules,UserRules::DELETE)) $buttons++;
			if ($this->parameters["indexable"]) $buttons+=2;
			$visibleColumns++;
		}
		$this->view->assign("useGrouping",false);
		if ($this->parameters["group_by"]) {
			foreach($this->parameters["columns"] as $idx=>$colDef) {
				if ($colDef["field"]==$this->parameters["group_by"]) {
					$this->view->assign("groupColumn",$idx);
					$this->view->assign("useGrouping",true);
					break;
				}
			}
		}
		$this->view->assign("buttonsColumnWidth",(40*$buttons));
		$this->view->assign("tableId",$this->name);
		$this->view->assign("columns",$this->parameters["columns"]);
		$this->view->assign("useData",$this->parameters["useData"]);
		$this->view->assign("noSort",$this->parameters["noSort"]);
		$this->view->assign("noSearch",$this->parameters["noSearch"]);
		$this->view->assign("title",$this->parameters["title"]);
		$this->view->assign("subtitle",$this->parameters["subtitle"]);
		$this->view->assign("indexable",$this->parameters["indexable"]);
		$this->view->assign("ajaxUrl",$this->parameters["url"]);
		$this->view->assign("formUrl",$this->parameters["form"]);
		$this->view->assign("breadcrumb",$this->parameters["breadcrumb"]);
		$this->view->assign("parent",$this->parameters["parent"]?'#'.$this->parameters["parent"]." ":'');
		//dump_var($this->parameters);
		$this->view->assign("extraButtons",$this->parameters["extra_buttons"]);
		$this->view->assign("actionToCall",($this->parameters["custom_action"]?$this->parameters["custom_action"]:'doAction'));

	}

}