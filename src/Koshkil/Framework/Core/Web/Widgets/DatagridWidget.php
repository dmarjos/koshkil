<?php
namespace Koshkil\Framework\Core\Web\Widgets;

use Koshkil\Framework\Core\Web\Widget;
use Koshkil\Framework\Core\Web\Support\Request;
use Koshkil\Framework\Core\Application;
use Koshkil\Framework\Support\Session\Auth;

class DatagridWidget extends Widget {

	private $grid_scripts;

	protected $parameters=array(
		"id_field"=>null,
		"tableId"=>"",
		"url"=>null,
		"form"=>null,
		"custom_action"=>null,
		"indexable"=>null,
		"no_update"=>null,
		"no_delete"=>null,
		"group_by"=>null,
		"columns"=>null,

		"onRow"=>null,
		"noSort"=>null,
		"group_row_class"=>null,
		"noSearch"=>null,
		"title"=>null,
		"subtitle"=>null,
		"breadcrumb"=>null,
		"parent"=>null,
		"extra_buttons"=>null,
	);

	private function checkScript($script) {
		return(in_array($script,$this->grid_scripts) || in_array($script,Application::get('scripts')));
	}

	protected function init(Request $request) {
		$buttons=0;
		if (($this->parameters["id_field"]&&($this->parameters["form"] || $this->parameters["custom_action"])) || ($this->parameters["indexable"])) {
			if (($this->parameters["id_field"]&&($this->parameters["form"] || $this->parameters["custom_action"])) && Auth::user()->hasRolesOrRules('Superuser',Application::$page->rules.".edit") && $this->parameters["no_update"]!==true) $buttons++;
			if (($this->parameters["id_field"]&&($this->parameters["form"] || $this->parameters["custom_action"])) && Auth::user()->hasRolesOrRules('Superuser',Application::$page->rules.".delete") && $this->parameters["no_delete"]!==true) $buttons++;
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

		$scripts=Application::get("scripts");
		$this->grid_scripts=Application::get("grid_scripts");
		if (!$this->grid_scripts) $this->grid_scripts=array();

		if (!in_array("/js/lib/core.js",$scripts)) {
			if (!in_array("/js/lib/core.js",$this->grid_scripts)) {
				$this->grid_scripts[]=Application::getLink('/js/lib/core.js');
				Application::addScript('/js/lib/core.js');
			}
		}

		if (!in_array("/js/lib/jquery.dataTables.js",$scripts)) {
			if (!in_array("/js/lib/jquery.dataTables.js",$this->grid_scripts)) {
				$this->grid_scripts[]=Application::getLink('/js/lib/jquery.dataTables.js');
				Application::addScript('/js/lib/jquery.dataTables.js');
			}
		}

		/*
		if (!in_array("/js/widgets/datagrid.js",$scripts)) {
			if (!in_array("/js/widgets/datagrid.js",$this->grid_scripts)) {
				$this->grid_scripts[]=Application::getLink('/js/widgets/datagrid.js');
				Application::addScript('/js/widgets/datagrid.js');
			}
		}
		*/

		$this->view->assign("grid_scripts",$this->grid_scripts);
		Application::set("grid_scripts",$this->grid_scripts);
		$this->view->assign("buttonsColumnWidth",(40*$buttons));
		$this->view->assign("tableId",$request->id);
		$this->view->assign("noDelete",$this->parameters["no_delete"]);
		$this->view->assign("noUpdate",$this->parameters["no_update"]);
		$this->view->assign("columns",$this->parameters["columns"]);
		$this->view->assign("onRowCreated",$this->parameters["onRow"]);
		$this->view->assign("noSort",$this->parameters["noSort"]);
		$this->view->assign("groupRowClass",$this->parameters["group_row_class"]);
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
		$this->view->assign("actionToCall",($this->parameters["custom_action"]?$this->parameters["custom_action"]:$this->name.'_doAction'));
	}

	public function __call($method,$parameters) {
		//dump_var(array($method,$parameters,$this->parameters,array_key_exists($method,$this->parameters)));
		if(array_key_exists($method,$this->parameters)) {
			$this->parameters[$method]=$parameters[0];
			return $this;
		}
	}

}