<?php
namespace Koshkil\Framework\Core\Traits\ModelDriven;

use Koshkil\Framework\Core\Web\Support\Request;
use Koshkil\Framework\Core\Exceptions\DB\ModelNotFound;
use Koshkil\Framework\Core\Application;

trait HandlesForms {
	use HandlesModels;

	public $mode="";
	public $fields= [];
	public $currentRecord=null;
	protected $model='';

	protected function initializeLifeCycle() {
		parent::initializeLifeCycle();
		$_lc=$this->lifeCycle;
		$this->lifeCycle=[];
		foreach($_lc as $cycle) {
			if ($cycle!="run") {
				$this->lifeCycle[]=$cycle;
			} else {
				$this->lifeCycle[]="handleCrud";
				$this->lifeCycle[]=$cycle;
			}
		}

	}

	protected function create(Request $request) {
		$this->loadModel($this->model);
	}

	protected function handleCrud(Request $request) {
		if (!$request->isPost()) {
			switch($request->action) {
				case "up":
					$retVal=$this->moveUp();
					break;
				case "down":
					$retVal=$this->moveDown();
					break;
				case "addnew":
					$this->mode="INSERT";
					$retVal=$this->prepareInsert($request);
					break;
				case "edit":
					$this->mode="UPDATE";
					$retVal=$this->prepareUpdate($request);
					break;
				case "delete":
					$this->mode="DELETE";
					$retVal=$this->prepareDelete($request);
					break;
				default:
					$retVal=$this->handleListings($request);
			}
		} else {
			switch($request->action) {
				case "addnew":
					$retVal=$this->insertRecord($request);
					break;
				case "edit":
					$this->mode="UPDATE";
					$retVal=$this->updateRecord($request);
					break;
				case "delete":
					$this->mode="DELETE";
					$retVal=$this->deleteRecord($request);
					break;
			}
		}
		return $retVal;
	}

	protected abstract function handleListings(Request $request);

	protected function recordToFields() {
		foreach($this->fields as $field => &$data) {
			if ($data["tableField"] && $this->currentRecord->hasField($data["tableField"]))
				$data["value"]=$this->currentRecord->decodeFieldValue($this->currentRecord[$data["tableField"]]);
		}
	}

	protected function insertRecord(Request $request) {}
	protected function updateRecord(Request $request) {}
	protected function deleteRecord(Request $request) {}

	protected function prepareInsert(Request $request) {}
	protected function prepareUpdate(Request $request) {}
	protected function prepareDelete(Request $request) {}

	protected function preInsertRecord(Request $request) {}
	protected function postInsertRecord(Request $request,$id) {}

	protected function preUpdateRecord(Request $request,$id) {}
	protected function postUpdateRecord(Request $request,$id) {}

	protected function preDeleteRecord(Request $request,$id) {}
	protected function postDeleteRecord(Request $request,$id) {}

	protected function moveUp() {}

	protected function moveDown() {}

	protected function canDeleteRecord() {
		return true;
	}
}