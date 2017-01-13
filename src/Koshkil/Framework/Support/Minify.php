<?php
namespace Koshkil\Framework\Core\Web\Support;

use Koshkil\Framework\Wrappers\File;
class Minify {
	static function processCss($path) {
		$cssFile=File::file_get_contents($path);
		preg_match_all("/url\(([^\)]*)\)/si",$cssFile,$matches);
		if ($matches) {
			foreach($matches[1] as $match) {
				$expectedPath=str_replace(array("'",'"'),"",$match);
				preg_match_all("!(\.\./)!si",$expectedPath,$upward);
				if ($upward) {
					$fullPath=explode("/",$path);
					for($x=1; $x<=count($upward[1])+1; $x++)
						array_pop($fullPath);
					$realPath=str_replace($_SERVER["DOCUMENT_ROOT"],"",implode("/",$fullPath)."/".substr($expectedPath,3*count($upward[1])));
					$cssFile=str_replace($expectedPath,$realPath,$cssFile);
				}
			}
		}
		$cssFile=preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','',$cssFile);
		$cssFile=str_replace(array("\n","\r\n","\r","\t"),"",$cssFile);
		return "/**\n *------------------------------------\n * ".str_replace($_SERVER["DOCUMENT_ROOT"],"",$path)."\n *------------------------------------\n */\n".$cssFile."\n";
	}
	static function processJs($path) {
		$jsFile=File::file_get_contents($path);
		if (strpos($path,".min.")) {
			return "/**\n *------------------------------------\n *---- Already minified ----\n * ".str_replace($_SERVER["DOCUMENT_ROOT"],"",$path)."\n *------------------------------------\n */\n".$jsFile."\n";
		} else {
			return "/**\n *------------------------------------\n * ".str_replace($_SERVER["DOCUMENT_ROOT"],"",$path)."\n *------------------------------------\n */\n".$jsFile."\n";
		}
	}
}