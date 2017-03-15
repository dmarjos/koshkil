<?php
namespace Koshkil\Framework\Core\Traits\Authentication;

use Koshkil\Framework\Core\Web\Support\Request;
use Koshkil\Framework\Support\Session\Auth;
use Koshkil\Framework\Core\Application;
use Koshkil\Framework\Support\Session;
use Koshkil\Framework\Support\Session\FlashMessages;

trait HandlesLogin {

	public $rules=null;
	public $strictRules=false;

	protected function initializeLifeCycle() {
		parent::initializeLifeCycle();
		$this->lifeCycle=array_merge(array_slice($this->lifeCycle,0,2),['authenticate'],array_slice($this->lifeCycle,2));
	}

	protected function authenticate(Request $request) {

		if (!Auth::check()) {
			if($request->isPost()) {
				if(!Auth::login($request)) {
					$this->templateFile="login.tpl";
					FlashMessages::setMessage(Session::get("LOGIN_MESSAGE"),FlashMessages::FLASH_TYPE_ERROR);
					return false;
				}
				return true;
			} else {
				$this->templateFile="login.tpl";
				return false;
			}
		} else {
			if ($this->strictRules && !Auth::user()->hasRules($this->rules)) {
				$this->templateFile="access_denied.tpl";
				return false;
			} else if(!Auth::user()->hasRolesOrRules("Superuser",$this->rules)) {
				$this->templateFile="access_denied.tpl";
				return false;
			} else
				return true;
			return false;
		}
	}

}