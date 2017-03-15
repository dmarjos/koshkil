<?php
namespace Koshkil\Framework\Routing;

use Koshkil\Framework\Support\StringUtils;
use Koshkil\Framework\Core\Application;

class RewriteManager {

	private static $rules=[
		["rule"=>'^(.*)/([0-9]*)/(edit|delete|up|down)$',"target"=>'$1?id=$2&action=$3',"flags"=>"L,NC","debug"=>false],
		["rule"=>'^(.*)/addnew$',"target"=>'$1?&action=addnew',"flags"=>"L,NC","debug"=>false],
		["rule"=>'^js/widgets/([^\.]*)\.js$',"target"=>'/?runComponent=widget&component=$1&method=javascript',"flags"=>"L,NC","debug"=>false],
		["rule"=>'^js/controllers/([^\.]*)\.js$',"target"=>'/$1?before_create=javascript',"flags"=>"NC","debug"=>false],
	];

	public static function addRule($expression,$rewrite,$flags="L,NC") {
		self::$rules[]=array("rule"=>$expression,"target"=>$rewrite,"flags"=>$flags);
	}

	public static function dumpRules() {
		dump_var(self::$rules);
	}
	public static function processRules() {
		$origUri=$uri=$_SERVER["REDIRECT_URL"];
		if (!$uri || $uri=="/index.php") $uri=$_SERVER["REQUEST_URI"];
		if (substr($uri,0,strlen(Application::get('BASE_DIR')))==Application::get('BASE_DIR'))
			$uri=substr($uri,strlen(Application::get('BASE_DIR')));
		if (substr($uri,0,1)=="/") $uri=substr($uri,1);
		$origUri=$uri;
		foreach(self::$rules as $rule) {
			$regExp='~'.$rule["rule"].'~s';
			if (in_array("NC",explode(",",$rule["flags"])))
				$regExp.="i";

			if (preg_match_all($regExp,$uri,$matches,PREG_SET_ORDER)) {
				$rewritedUrl=$rule["target"];
				if (preg_match_all('~(\$([0-9]*))~',$rule["target"],$replacements,PREG_SET_ORDER)) {
					foreach($replacements as $replacement) {
						$rewritedUrl=StringUtils::replace_all($replacement[1],$matches[0][intval($replacement[2])],$rewritedUrl);
					}
				}
				list($newURI,$qs)=explode("?",$rewritedUrl);
				$newURI=Application::GetLink($newURI);
				$oldQS=$_SERVER["REDIRECT_QUERY_STRING"];
				if ($oldQS) $qs.="&".$oldQS;
				$_SERVER["REDIRECT_URL"]=$newURI;
				$_SERVER["REDIRECT_QUERY_STRING"]=$qs;
				if ($qs) parse_str($qs,$_GET);


				if (in_array("L",explode(",",$rule["flags"]))) {
					break;
				} else if (in_array("RW",explode(",",$rule["flags"]))) {
					$uri=$_SERVER["REDIRECT_URL"];
					if (substr($uri,0,1)=="/") $uri=substr($uri,1);
				}
			}

		}
	}

}