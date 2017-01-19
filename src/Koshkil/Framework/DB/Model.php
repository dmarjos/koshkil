<?php
namespace Koshkil\Framework\DB;

use Koshkil\Framework\DB\QueryBuilder;
use Koshkil\Framework\Support\StringUtils;
use Koshkil\Framework\Core\Application;

class Model implements \ArrayAccess {

	private $qb;

	// On which table it shall work
	protected $table="";

	protected $alias="";

	// Which fields will be filled with data
	protected $fillable=array();

	// Which fields will not be encoded when filled with data
	protected $skipEncoding=array();

	// Which fields are dates or datetimes
	protected $dates=array();

	// Fields values
	public $attributes=array();

	// The primary index field
	protected $indexField="";


	public function __construct() {
		$this->initBuilder();
	}

	public function initBuilder() {
		$this->qb=new QueryBuilder();
		$this->qb->clear();
		$this->qb->from($this->table.($this->alias!=""?" as ".$this->alias:""));
		return $this;
	}

	public function builder() {
		return $this->qb;
	}

	protected function belongsTo() {
		$parameters=func_get_args();
		$relatedModel=array_shift($parameters);

		$modelNamespace="/Models/".$relatedModel;
		$model=Application::get("APPLICATION_DIR")."{$modelNamespace}";
		$modelNamespace="app";
		if(Application::get("APP_NAME"))
			$modelNamespace.="\\".Application::get("APP_NAME");
		$modelNamespace.="\\Models\\{$relatedModel}";
		//dump_var($model);
		require_once($model.".php");
		$dummyModel=new $modelNamespace();
		$localValue=array_shift($parameters);
		$relatedPrimaryKey=array_shift($parameters);

		if (is_null($relatedPrimaryKey) && is_null($localValue)) {
			return $modelNamespace::find($this->{$modelNamespace::getIndexField()});
		} else if (!is_null($localValue) && is_null($relatedPrimaryKey)) {
			return $modelNamespace::where($modelNamespace::getIndexField(),$this->{$localValue})->first();
		} else {
			return $modelNamespace::where($relatedPrimaryKey,$this->{$localValue})->first();
		}
	}

	public static function getIndexField() {
		$dummy=new static;
		$retVal=$dummy->indexField;
		unset($dummy);
		return $retVal;
	}

	public function fill($data,$prefix="") {
		foreach($data as $field=>$value) {
			if ($prefix) $field=$prefix.$field;
			if (in_array($field,$this->fillable))
				$this->setAttribute($field,$value);
		}
		return $this;
	}

	public function hasField($fieldName) {
		return isset($this->attributes[$fieldName]);
	}

	public static function create($data) {
		$instance = new static;

		//$record=array($instance->indexField=>$instance[$instance->indexField]);
		foreach($data as $field=>$value) {
			if (in_array($field,$instance->fillable))
				$instance->{$field}=$value;
		}
		return $instance->builder()->insert($instance->record(),get_class($instance));
	}

	public function update() {
		$record=array($this->indexField=>$this[$this->indexField]);
		foreach($this->attributes as $field=>$value) {
			if (in_array($field,$this->fillable))
				$record[$field]=$value;
		}
		return $this->initBuilder()->builder()->update($record,get_class($this));
	}

	public function delete() {
		if ($this->indexField && $this[$this->indexField]) {
			$this->initBuilder()->builder()->where($this->indexField,$this[$this->indexField])->delete();
		}
		return;
	}

	public function record() {
		return $this->attributes;
	}

	public function encodeFieldValue($value) {
		if (!is_string($value)) return $value;
//		if (StringUtils::hasUTF8Chars($value))
//			$value=utf8_encode($value);

		if(ini_get('default_charset')=="UTF-8")
			$value=utf8_decode($value);
		$value=htmlentities($value,ENT_COMPAT | ENT_HTML401,"ISO-8859-1");
		$value=StringUtils::replace_all("[:euro:]","&euro;",$value);
		$value=StringUtils::replace_all("&lt;","<",$value);
		$value=StringUtils::replace_all("&gt;",">",$value);
		$value=StringUtils::replace_all("&amp;","&",$value);
		$value=StringUtils::replace_all("&quot;",'"',$value);
		return $value;
	}

	public function decodeFieldValue($value) {
		$value=StringUtils::replace_all("&euro;","[:euro:]",$value);
		return $value;
	}

	private function getAttribute($attribute) {
		list($fieldName,$function)=explode("|",$attribute);
		$retVal=$this->attributes[$fieldName];
		//$retVal=$this->decodeFieldValue($retVal);
		if (!empty($function)) $retVal=$function($retVal);
		if (in_array($fieldName,$this->dates)) {
			if (preg_match_all("/([0-9]{4})\-([0-9]{2})\-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/si",$retVal,$matches)) {
				list($d,$t)=explode(" ",$retVal);
				$d=implode("/",array_reverse(explode("-",$d)));
				$retVal=$d.($t!="00:00:00"?" ".$t:'');
			} else if (preg_match_all("/([0-9]{4})\-([0-9]{2})\-([0-9]{2})/si",$retVal,$matches)) {
				$retVal=implode("/",array_reverse(explode("-",$retVal)));
			}
		}
		return $retVal;
	}

	public function rawAttribute($attribute) {
		list($fieldName,$function)=explode("|",$attribute);
		$retVal=$this->attributes[$fieldName];
		//$retVal=$this->decodeFieldValue($retVal);
		if (!empty($function)) $retVal=$function($retVal);
		return $retVal;
	}

	private function setAttribute($attribute,$value) {
		if (!in_array($attribute,$this->skipEncoding))
			$value=$this->encodeFieldValue($value);

		if (in_array($attribute,$this->dates)) {
			$dt=preg_match_all("/([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/si",$value,$matches) || preg_match_all("/([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2}):([0-9]{2})/si",$value,$matches) || preg_match_all("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/si",$value,$matches);
			if ($dt) {
				list($d,$t)=explode(" ",$value);
				$d=implode("-",array_reverse(explode("/",$d)));
				if ($t) {
					$hms=explode(":",$t);
					for($i=count($hms); $i<3; $i++) $hms[]="00";
					$t=implode(":",$hms);
				}
				$value=trim($d." ".$t);
			} else if (preg_match_all("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/si",$value,$matches)) {
				$value=implode("/",array_reverse(explode("-",$value)));
			}
		}
		$this->attributes[$attribute]=$value;
		return $value;
	}

	public static function __callStatic($method,$parameters) {
		$instance = new static;
		if (method_exists($instance,$method))
			return call_user_func_array(array($instance, $method), $parameters);
		else if (method_exists($instance->builder(),$method)) {
			if (is_array($parameters))
				$parameters[]=get_class($instance);
			else
				$parameters=array(get_class($instance));
			return call_user_func_array(array($instance->builder(), $method), $parameters);
		}
		throw new Exception("TModel error. Method not found");
	}

	public function offsetExists ($offset) {
		return isset($this->attributes[$offset]);
	}

	/**
	 * @param offset
	 */
	public function offsetGet ($offset) {
		list($fieldName,$function)=explode("|",$offset);
		if (isset($this->attributes[$fieldName])) {
			return $this->getAttribute($offset);
		} else
			return null;

	}

	/**
	 * @param offset
	 * @param value
	 */
	public function offsetSet ($offset, $value) {
		$this->setAttribute($offset,$value);
	}

	/**
	 * @param offset
	 */
	public function offsetUnset ($offset) {
		unset($this->attributes[$offset]);
	}

	public function __get($varName) {
		if (method_exists($this,$varName))
			return call_user_func(array($this,$varName));
		else if (isset($this->attributes[$varName]))
			return $this->getAttribute($varName);
		return null;
	}

	public function __set($varName,$value) {
		return $this->setAttribute($varName,$value);
	}

	public function __toString() {
		return serialize($this->attributes);
	}


}
