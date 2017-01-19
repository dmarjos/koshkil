<?php
namespace Koshkil\Framework\Core\Exceptions;

class PHPError extends Exception {
	/**
	 * Class used to translate PHP errors
	 * to an exception that can be handle with the
	 * global exception handler in dmFramework
	 */
	public $types = array(
			E_ERROR           => "Error",
			E_WARNING         => "Warning",
			E_PARSE           => "Parsing Error",
			E_NOTICE          => "Notice",
			E_CORE_ERROR      => "Core Error",
			E_CORE_WARNING    => "Core Warning",
			E_COMPILE_ERROR   => "Compile Error",
			E_COMPILE_WARNING => "Compile Warning",
			E_USER_ERROR      => "User Error",
			E_USER_WARNING    => "User Warning",
			E_USER_NOTICE     => "User Notice",
			E_STRICT          => "Runtime Notice"
	);

	public $errno;
	public $errstr;
	public $errfile;
	public $errline;

	public function __construct($errno, $errstr, $errfile, $errline) {
		$this->errfile = $errfile;
		$this->errline = $errline;
		$this->errno = $errno;
		$this->errstr = $errstr;
		parent::__construct($errstr, $errno);
	}

}