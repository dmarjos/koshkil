<?php
namespace Koshkil\Framework\Core\Traits\Ajax;

use Koshkil\Framework\Core\Web\Support\Request;
use Koshkil\Framework\Support\Session\Auth;

trait HandlesAjax {

	protected $data;

	protected function init(Request $request) {
		if (parent::init($request)) {
			if (!$request->type)
				$request->type="json";
			if ($request->method && method_exists($this,$request->method))
				$retVal=call_user_func(array($this,$request->method),$request);
		}
		return $retVal;
	}

	protected function output(Request $request) {
		if (!Auth::check()) {
			$this->data=["status"=>"fail","reason"=>"Access denied"];
		}

		if ($request->type=="json" && (is_array($this->data) || is_object($this->data))) {
			header("Content-Type: text/json; charset=utf8");
			die(json_encode($this->data));
		} else if ($request->type=="json_text" && (is_array($this->data) || is_object($this->data))) {
			die(json_encode($this->data));
		} else {
			die($this->data);
		}
	}

}
