<?php
namespace Koshkil\Framework\Support\Session;


use Koshkil\Framework\Support\Session;
use Koshkil\Framework\DB\Models\User;
use Koshkil\Framework\Core\Web\Support\Request;
use Koshkil\Framework\Core\Application;
use Koshkil\Framework\Core\Exceptions\UnkownUserKind;
use Koshkil\Framework\Support\PasswordUtils;

class Auth extends Session {

	protected static $failedLoginMessage="The credentials doesn't match with our records";
	protected static $successfulLoginMessage="Successful Login!";
	protected static $currentUser=[
		"backend"=>false,
		"frontend"=>false
	];

	protected static $credentials=[
		"username"=>"usr_username","password"=>"usr_password|password"
	];

	protected static $userModel="Koshkil\Framework\DB\Models\User";
	protected static $authNamespace="frontend";

	public static function setFailedLoginMessage($message) {
		self::$failedLoginMessage=$message;
	}
	public static function setUserModel($userModel) {
		self::$userModel=$userModel;
	}

	public static function setCredentials($credentials) {
		self::$credentials=$credentials;
	}

	public static function setAuthNamespace($area) {
		if (!isset($area,self::$currentUser))
			throw new UnkownUserKind("Auth::setAuthNamespace() needs one of the following: \"".implode('","',array_keys(self::$currentUser))."\". {$area} was provided");

		self::$authNamespace=$area;
	}

	public static function check() {
		if(self::get("CURRENT_USER"))
			self::$currentUser=self::get("CURRENT_USER");
		$area=self::$authNamespace;
		if (!isset($area,self::$currentUser))
			throw new UnkownUserKind("Auth::check() needs one of the following: \"".implode('","',array_keys(self::$currentUser))."\"");

		return is_object(self::$currentUser[$area]) && (self::$currentUser[$area] instanceof User);
	}

	public static function user() {
		$area=self::$authNamespace;
		return self::$currentUser[$area];
	}

	public static function login(Request $request, $credentials=[]) {
		if (empty($credentials)) {
			$credentials=self::$credentials;
		}
		$modelPath=explode("\\",self::$userModel);
		$rootNamespace=array_shift($modelPath);
		if ($rootNamespace=="App") $rootNamespace="app";
		array_unshift($modelPath, $rootNamespace);
		$modelPath=implode("/",$modelPath);
		require_once(Application::get("PHYS_PATH")."/".$modelPath.".php");
		$userModel=new self::$userModel();
		$userQB=$userModel->builder();
		foreach($credentials as $reqField => $tblField) {
			$tblFieldParameters=explode("|",$tblField);
			$tblField=$tblFieldParameters[0];
			if (isset($tblFieldParameters[1])) {
				if ($tblFieldParameters[1]!="password")
					$userQB->where($tblField,$request->{$reqField});
			} else
				$userQB->where($tblField,$request->{$reqField});
		}
		$user=$userQB->first(get_class($userModel));
		foreach($credentials as $reqField => $tblField) {
			$tblFieldParameters=explode("|",$tblField);
			$tblField=$tblFieldParameters[0];
			if (isset($tblFieldParameters[1])) {
				if ($tblFieldParameters[1]=="password") {
					$passOk=PasswordUtils::createHash($request->{$reqField},$user->{$tblField});
					if ($passOk) {
						$area=self::$authNamespace;
						self::$currentUser[$area]=$user;
						self::set("LOGIN_MESSAGE",self::$successfulLoginMessage);
						self::saveSession();
						return true;
					} else {
						self::set("LOGIN_MESSAGE",self::$failedLoginMessage);
						self::saveSession();
						return false;
					}
				}
			}
		}
		$area=self::$authNamespace;
		self::$currentUser[$area]=$user;
		self::set("LOGIN_MESSAGE",self::$successfulLoginMessage);
		self::saveSession();
		return true;
	}

	private static function saveSession() {
		self::set("CURRENT_USER",self::$currentUser);
	}
}