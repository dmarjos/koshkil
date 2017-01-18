<?php
namespace Koshkil\Framework\DB;

use Koshkil\Framework\Support\Collection;
use Koshkil\Framework\Core\Application;

class QueryBuilder {
	private $db;
	private $_compiled=false;
	private $_fields="";
	private $_table="";
	private $_mainAlias="";
	private $_joins=array();
	private $_joinedTables="";
	private $_where=array();
	private $_having=array();
	private $_orderBy="";
	private $_groupBy="";
	private $_offset=-1;
	private $_limit=-1;
	private $_className = null;
	public $totalRecords=0;
	public $affectedRecords=0;

	private $debug=false;

	private $_collectionClass="Collection";

	public function clear() {
		$this->_compiled=false;
		$this->_fields="";
		$this->_table="";
		$this->_mainAlias="";
		$this->_joinedTables="";
		$this->_debug=false;
		$this->_joins=array();
		$this->_where=array();
		$this->_having=array();
		$this->_groupBy="";
		$this->_orderBy="";
		$this->_offset=-1;
		$this->_limit=-1;
		$this->_instance=null;
		$this->totalRecords=0;
		$this->affectedRecords=0;
		return $this;
	}

	public function setAlias($alias,$className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		$this->_mainAlias=$alias;
	}
	public function select($fields,$className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		if (!is_array($this->_fields) && !empty($this->_fields)) {
			$this->_fields=array($this->_fields,$fields);
		} else if(is_array($this->_fields))
			$this->_fields[]=$fields;
		else
			$this->_fields=$fields;
		return $this;
	}

	public function withCollection($collectionClass,$className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;
		if (is_string($collectionClass) && class_exists($collectionClass,false))
			$this->_collectionClass=$collectionClass;
		return $this;
	}

	public function from($table,$className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		if (!is_array($this->_table) && !empty($this->_table)) {
			$this->_table=array($this->_table,$table);
		} else if(is_array($this->_table))
			$this->_table[]=$table;
		else
			$this->_table=$table;
		return $this;
	}

	public function order() {
		$parameters=func_num_args();

		if (is_string(func_get_arg($parameters-1)) && class_exists(func_get_arg($parameters-1),false)) {
			$this->_className=func_get_arg($parameters-1);
			$parameters--;
		}
		switch($parameters) {
			case 2:
				$orderBy=func_get_arg(0)." ".func_get_arg(1)."";
				break;
			case 1:
				$orderBy=func_get_arg(0);
				break;
			default:
				throw new TQueryBuilderException("ORDER malformed. (".serialize($parameters).")");
		}
		if (!is_array($this->_orderBy) && !empty($this->_orderBy)) {
			$this->_orderBy=array($this->_orderBy,$orderBy);
		} else if(is_array($this->_orderBy))
			$this->_orderBy[]=$orderBy;
		else
			$this->_orderBy=$orderBy;
		return $this;
	}

	public function group($groupBy,$className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		if (!is_array($this->_groupBy) && !empty($this->_groupBy)) {
			$this->_groupBy=array($this->_groupBy,$groupBy);
		} else if(is_array($this->_groupBy))
			$this->_groupBy[]=$groupBy;
		else
			$this->_groupBy=$groupBy;
		return $this;
	}

	public function where() {
		$parameters=func_num_args();
		if (is_string(func_get_arg($parameters-1)) && class_exists(func_get_arg($parameters-1),false)) {
			$this->_className=func_get_arg($parameters-1);
			$parameters--;
		}

		switch($parameters) {
			case 3:
				if (is_array(func_get_arg(2)))
					$where=func_get_arg(0)." ".func_get_arg(1)." ('".implode("', '",func_get_arg(2))."')";
				else
					$where=func_get_arg(0)." ".func_get_arg(1)." '".func_get_arg(2)."'";
				break;
			case 2:
				$where=func_get_arg(0)."='".func_get_arg(1)."'";
				break;
			case 1:
				$where=func_get_arg(0);
				break;
			default:
				throw new TQueryBuilderException("Where condition malformed");
		}
		if (is_array($where))
			$this->_where=array_merge($this->_where,$where);
		else
			$this->_where[]=$where;
		return $this;
	}

	public function having() {
		$parameters=func_num_args();
		if (is_string(func_get_arg($parameters-1)) && class_exists(func_get_arg($parameters-1),false)) {
			$this->_className=func_get_arg($parameters-1);
			$parameters--;
		}

		switch($parameters) {
			case 3:
				if (is_array(func_get_arg(2)))
					$having=func_get_arg(0)." ".func_get_arg(1)." ('".implode("', '",func_get_arg(2))."')";
				else
					$having=func_get_arg(0)." ".func_get_arg(1)." '".func_get_arg(2)."'";
				break;
			case 2:
				$having=func_get_arg(0)."='".func_get_arg(1)."'";
				break;
			case 1:
				$having=func_get_arg(0);
				break;
			default:
				throw new TQueryBuilderException("Having condition malformed");
		}
		if (is_array($having))
			$this->_having=array_merge($this->_having,$having);
		else
			$this->_having[]=$having;
		return $this;
	}

	public function offset($offset,$className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		$this->_offset=$offset;
		return $this;
	}

	public function take($limit,$className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		$this->_limit=$limit;
		return $this;
	}

	public function join() {
		//$joinedTable,$joinCondition,$type="inner",$className=null
		$parameters=func_num_args();
		if (is_string(func_get_arg($parameters-1)) && class_exists(func_get_arg($parameters-1),false)) {
			$this->_className=func_get_arg($parameters-1);
			$parameters--;
		}

		switch ($parameters) {
			case 3:
				$type=func_get_arg(2);
				$joinCondition=func_get_arg(1);
				$joinedTable=func_get_arg(0);
				break;
			case 2:
				$type="inner";
				$joinCondition=func_get_arg(1);
				$joinedTable=func_get_arg(0);
				break;
		}

		$this->_joins[]=array("table"=>$joinedTable,"on"=>"1","condition"=>$joinCondition,"type"=>$type);
		return $this;
	}

	public function joinUsing() {
		$parameters=func_num_args();
		if (is_string(func_get_arg($parameters-1)) && class_exists(func_get_arg($parameters-1),false)) {
			$this->_className=func_get_arg($parameters-1);
			$parameters--;
		}

		switch ($parameters) {
			case 3:
				$type=func_get_arg(2);
				$joinCondition=func_get_arg(1);
				$joinedTable=func_get_arg(0);
				break;
			case 2:
				$type="inner";
				$joinCondition=func_get_arg(1);
				$joinedTable=func_get_arg(0);
				break;
		}

		$this->_joins[]=array("table"=>$joinedTable,"using"=>"1","condition"=>$joinCondition,"type"=>$type);
		return $this;
	}

	public function compile($className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		if (is_array($this->_fields))
			$this->_fields=implode(",",$this->_fields);

		if (empty($this->_fields)) $this->_fields="*";

		if (is_array($this->_table))
			$this->_table=implode(",",$this->_table);

		if (is_array($this->_orderBy)) {
			$__o=array();
			foreach($this->_orderBy as $order) {
				if (substr($order,0,2)!="r ") $__o[]=$order;
			}
			$this->_orderBy=implode(",",$__o);
		}

		if (is_array($this->_groupBy))
			$this->_groupBy=implode(",",$this->_groupBy);

		if (is_array($this->_having))
			$this->_having=implode(",",$this->_having);

		$this->_joinedTables="";
		if (!empty($this->_joins)) {
			foreach($this->_joins as $join) {

				$joinStmt=strtoupper($join["type"])." JOIN ".$join["table"]." ".($join["on"]?"ON":"USING")." (".$join["condition"].")";
				$this->_joinedTables.=" ".$joinStmt;
			}
		}
		$this->_compiled=true;
		$sql=Application::$db->getSQL(array("debug"=>$this->_debug,"fields"=>$this->_fields,"table"=>$this->_table.$this->_joinedTables,"grouping"=>$this->_groupBy,"having"=>$this->_having),$this->_where,$this->_orderBy,$this->_offset,$this->_limit);
		return $sql;
	}

	public function debug($enable,$className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		$this->_debug=$enable;
		return $this;
	}

	public function get($className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		if (!$this->_compiled)
			$sql=$this->compile();

		if ($this->_debug) error_log("[SQL DEBUG]:{$sql}");
		$records=Application::$db->getRecords(array("fields"=>$this->_fields,"table"=>$this->_table.$this->_joinedTables,"grouping"=>$this->_groupBy,"having"=>$this->_having),$this->_where,$this->_orderBy,$this->_offset,$this->_limit);
		$this->affectedRecords=count($records["data"]);
		$this->totalRecords=intval($records["records"]);
		if (is_string($this->_className) && class_exists($this->_className,false)) {
			$retVal=new $this->_collectionClass();
			$retVal->totalRecords=intval($records["records"]);
			$retVal->affectedRecords=count($records["data"]);
			foreach($records["data"] as $record) {
				$_record=new $this->_className();
				foreach($record as $field => $value) {
					$_record->{$field}=$value;
				}
				$retVal->addItem($_record);
			}
		} else
			$retVal=$records["data"];
		return $retVal;
	}

	public function getAsArray() {
		$this->_className=null;
		return $this->get(null);
	}
	public function getCombo($selected,$textField,$className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		if (!is_array($selected)) $selected=array($selected);
		$records=$this->get();

		$retVal=array();
		foreach($records as $record) {
			if (is_string($textField))
				$text=trim("{$record[$textField]}");
			else if(is_array($textField) && is_object($textField[0]) && method_exists($textField[0],$textField[1]))
				$text=call_user_func($textField,$record);
			if (empty($text)) continue;
			$sel="";
			if (in_array($record[$record->indexField],$selected)) $sel=' selected="selected"'; else $sel='';
			$retVal[]="<option value=\"".$record[$record->indexField]."\"{$sel}>{$text}</option>";
		}
		return implode("\n",$retVal);
	}

	public function dataTable() {

		$computedFields=array();

		if (!is_array($this->_fields))
			$_fields=array($this->_fields);
		else
			$_fields=$this->_fields;

		foreach($_fields as $_field){
			$fields=explode(",",$_field);
			foreach($fields as $field) {
				preg_match_all("/ as ([`0-9a-z_]*?)/Usi",$field,$matches,PREG_SET_ORDER);
				if ($matches)
					foreach($matches as $match) $computedFields[]=$match[1];
			}
		}
		$parameters=func_num_args();
		if (is_string(func_get_arg($parameters-1)) && class_exists(func_get_arg($parameters-1),false)) {
			$this->_className=func_get_arg($parameters-1);
			$parameters--;
		}

		if ($parameters==2)
			$formatters=func_get_arg(1);

		$columns=func_get_arg(0);
		if($_POST['iDisplayStart'])
			$this->_offset=$_POST['iDisplayStart'];
		if($_POST['iDisplayLength'])
			$this->_limit=$_POST['iDisplayLength'];

		for ( $i=0 ; $i<intval( $_POST['iSortingCols'] ) ; $i++ ) {
			if ( $_POST[ 'bSortable_'.intval($_POST['iSortCol_'.$i]) ] == "true" && !empty($columns[ intval( $_POST['iSortCol_'.$i] ) ])) {
				$this->order($columns[ intval( $_POST['iSortCol_'.$i] ) ],($_POST['sSortDir_'.$i]==='asc' ? 'asc' : 'desc'));
			}
		}

		$searchW=$searchH=array();
		if ( isset($_POST['sSearch']) && $_POST['sSearch'] != "" ) {

			for ( $i=0 ; $i<count($columns) ; $i++ ) {
				if (in_array($columns[$i],$computedFields))
					$searchH[]= "`".$columns[$i]."` LIKE '%".Application::escape($_POST['sSearch'])."%'";
				else
					$searchW[]= "`".$columns[$i]."` LIKE '%".Application::escape($_POST['sSearch'])."%'";
			}
		}

		if (!empty($searchW))
			$this->where("(".implode (" OR ",$searchW).")");

		if (!empty($searchH))
			$this->having("(".implode (" OR ",$searchH).")");

		for ( $i=0 ; $i<count($columns) ; $i++ ) {
			if ( isset($_POST['bSearchable_'.$i]) && $_POST['bSearchable_'.$i] == "true" && $_POST['sSearch_'.$i] != '' ) {
				$this->where($columns[$i],'LIKE',"%".Application::escape($_POST['sSearch_'.$i])."%");
			}
		}


		$retVal=$this->get();
		$output=array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $retVal->totalRecords,
			"iTotalDisplayRecords" => $retVal->totalRecords,
			"aaData" => array()
		);
		foreach($retVal as $record) {
			$indexField=$record->indexField;
			$aaData=array();
			foreach($columns as $col) $aaData[]=utf8_encode($record->{$col});
			$aaData[]=$record->{$indexField};
			$output["aaData"][]=$aaData;
		}
		return $output;
	}

	public function first($className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		$retVal=$this->get();
		return $retVal[0];
	}

	public function find($value,$className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		if (!is_null($this->_className)) {
			$auxModel=new $this->_className;
			return $this->where($auxModel->indexField,$value)->first();
		}
	}

	public function insert($data,$className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		if (!$this->_compiled)
			$sql=$this->compile();

		$primaryKey=Application::$db->insert($this->_table,$data);

		$auxModel=new $this->_className;
		if ($auxModel->indexField)
			return $this->where($auxModel->indexField,$primaryKey)->first();

		return null;

	}

	public function update($data,$className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		if (!$this->_compiled)
			$sql=$this->compile();

		$auxModel=new $this->_className;

		$primaryKey=Application::$db->update($this->_table,$data,array($auxModel->indexField."=".$data[$auxModel->indexField]));
		return $this->where($auxModel->indexField,$data[$primaryKey])->first();
	}

	public function delete() {
		if (empty($this->_where)) $this->empty(array($auxModel->indexField."=".$data[$auxModel->indexField]));
		Application::$db->delete($this->_table, $this->_where);
		return true;
	}

	public function asArray() {
		$this->_className=null;
		return $this;
	}

	public function asModel($className) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;
		return $this;
	}

	public function each($callback,$className=null) {
		if (is_string($className) && class_exists($className,false))
			$this->_className=$className;

		foreach($this->get() as $record) {
			call_user_func($callback,$record);
		}
	}
}