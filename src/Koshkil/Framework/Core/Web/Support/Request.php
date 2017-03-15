<?php
namespace Koshkil\Framework\Core\Web\Support;

use Koshkil\Framework\Core\Application;
class Request implements \ArrayAccess {

	private $items=[];
	private $isPost=false;

	public function __construct($src=[]) {
		foreach($_GET as $key=>$val) {
			$this->addItem($val,$key);
		}
		if ($_POST) {
			$this->isPost=true;
			foreach($_POST as $key=>$val) {
				$this->addItem($val,$key);
			}
		}
		if ($src) {
			foreach($src as $key=>$value)
				$this->addItem($value,$key);
		}
	}

	/**
	 * returns TRUE if the request is a form POST, false otherwise
	 */
	public function isPost() {
		return $this->isPost;
	}

	/**
	 * returns TRUE if the request is a form POST and has files attached, false otherwise
	 */
	public function hasFiles() {
		return $this->isPost() && (isset($_FILES) && !empty($_FILES));
	}

	/**
	 * Returns the associative index names for uploaded files
	 */
	public function files() {
		return array_keys($_FILES);
	}

	public function redirect($uri) {
		header("location: ".Application::getAsset($uri));
		die();
	}
	/**
	 * This is a wrapper meant to get the uploaded files in a coherent way thru code.
	 *
	 * @param string $field. The field name as seen in the $_FILES superglobal.
	 * @param number $index. If uploading multiple files (&ltinput type="file" name="myfile[]"/&gt), this is the file numer to fetch
	 * @return array. Returns the proper file
	 */
	public function file(string $field,$index=0) {
		if(isset($_FILES[$field])) {
			if (is_array($_FILES[$field]["name"])) {
				if (is_nan($index)) $index=0;
				return [
					"name"=>$_FILES[$field]["name"][$index],
					"type"=>$_FILES[$field]["type"][$index],
					"size"=>$_FILES[$field]["size"][$index],
					"error"=>$_FILES[$field]["error"][$index],
					"tmp_name"=>$_FILES[$field]["tmp_name"][$index],
				];
			} else {
				return $_FILES[$field];
			}
		}
	}
	private function addItem($item,$key) {
		$this->items[$key]=$item;
	}

	public function getKeys() {
		return array_keys($this->items);
	}

	public function __toString() {
		return serialize($this->items);
	}

	public function __get($item) {
		return isset($this->items[$item])?$this->items[$item]:null;
	}
	public function __set($item,$value) {
		$this->items[$item]=$value;
	}

	public function offsetExists ($offset) {
		return isset($this->items[$offset]);
	}

	public function offsetGet ($offset) {
		if (isset($this->items[$offset]))
			return $this->items[$offset];
		else
			return null;

	}

	public function offsetSet ($offset, $value) {
		$this->items[$offset]=$value;
	}

	public function offsetUnset ($offset) {
		unset($this->items[$offset]);
	}
}