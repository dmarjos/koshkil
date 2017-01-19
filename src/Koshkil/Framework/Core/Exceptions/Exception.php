<?php
namespace Koshkil\Framework\Core\Exceptions;

class Exception extends \Exception {
	public $details = '';

	public function __construct($msg, $errno=0, $details='') {
		$this->details = $details;
		parent::__construct($msg, $errno);
	}

	static public function getErrorMessage($e,$advanced=false) {
		$msg = "\n".'<exception><pre style="border-left:1px solid #ccc;padding-left:10px;margin:5px 0px">';
		if ($advanced || Config::DEBUG_MODE) {
			$msg .= '[<b style="color:#c00">'.get_class($e).($e instanceof PHPError ? ': '.(array_key_exists($e->errno, $e->types) ? $e->types[$e->errno] : 'Unknown') : '').'</b>]<br />';
		}
		$msg .= $e->getMessage();
		if ($advanced || Config::DEBUG_MODE) {
			$msg .= $e instanceof EPHPError ? '<br />#in '.$e->errfile.' ('.$e->errline.')' : '<br />#in '.$e->getFile().' ('.$e->getLine().')';
			if ($e instanceof TException) { $msg .= '<pre style="padding:5px;margin:0px;display:block">'.$e->details."</pre>"; }
			$msg .= '<pre style="padding:0px;margin:0px;display:block">'.htmlspecialchars($e->getTraceAsString())."</pre>";
		}
		return $msg.'</pre></exception>';
	}
}