<?php
namespace Koshkil\Framework\Support;

class StringUtils {

	public function countWords($string) {
		$string=preg_replace('/[^a-z0-9]+/',"-",$string);
		$string=$this->replace_all("--","-",$string);
		if (substr($string,-1)=="-") {
			$string=substr($string,0,-1);
		}
		$words=explode("-",$string);
		return count($words);
	}

	public static function makeClickableLinks($source) {
		return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $source);
	}

	public static function makePlainString($string,$skipUtf8=false) {
		$string=strip_tags($string);
		if (self::hasUTF8Chars($string) && !$skipUtf8)
			$string=utf8_decode(strtolower($string));
		$string=preg_replace('~&([a-z]{1,2})(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo|orn|th);~i', '$1', htmlentities($string) );//;
		$string = preg_replace('/\n|\r/',' ',trim(strtolower($string)));
		$string = preg_replace('/\.+/',' ',$string);
		$string = preg_replace('/ +/','_',$string);
		$string = preg_replace('/([^a-z0-9\._-])/','', $string);

		return $string;
	}

	public static function safeName($name, $path, $cut=75) {

		$extension=substr($name,strrpos($name,".")+1);
		$name=basename($name,".".$extension);
		$name=preg_replace('~&([a-z]{1,2})(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|orn|th);~i', '$1', htmlentities($name) );//;
		$name = preg_replace('/\n|\r/',' ',trim(strtolower($name)));
		$name = preg_replace('/\.+/',' ',$name);
		$name = preg_replace('/ +/','_',$name);
		$name = preg_replace('/([^a-z0-9\._-])/','', $name);
		$name = substr($name, 0, $cut);

		$name=trim($name);
		if (file_exists($path."/".$name.".".$extension)) {
			$idx=1;
			while (file_exists($path."/".$name."_".$idx.".".$extension))
				$idx++;
			$name.="_".$idx;
		}
		return (trim($name) == '' ? 'unknown' : trim($name)).".".$extension;
	}


	public static function makeURL($string,$stopWords=[]) {
		foreach($stopWords as &$word) $word=trim($word);
		$string=self::makePlainString($string,true);
		$string=preg_replace('/\b('.implode('|',$stopWords).')\b/','',$string);
		$string=trim($string);
		$string=self::replace_all("  "," ",$string);
		$string=preg_replace('/[^a-z0-9]+/',"-",$string);
		$string=self::replace_all("--","-",$string);
			if (substr($string,-1)=="-") {
			$string=substr($string,0,-1);
		}

		if (substr($string,0,1)=="-") {
			$string=substr($string,1);
		}

		return $string;
	}

	public static function hasUTF8Chars($str) {
		if (!is_string($str)) return false;
		for($i=0; $i<strlen($str);$i++) {
			if (ord($str[$i])==194 || ord($str[$i])==195) return true;
		}
		return false;
	}
	public function replace_all($search,$replacement,$subject) {
		if (!is_string($subject)) return $subject;
		while(strpos($subject,$search)!==false) {
			$subject=str_replace($search,$replacement,$subject);
		}
		return $subject;
	}

	public static function makeExcerpt($str, $length = 200, $etc = '...') {
		if (strpos($str,"<hr>")!==false)
			list($excerpt,$rest)=explode("<hr>",$str);
		elseif (strpos($str,"<hr/>")!==false)
			list($excerpt,$rest)=explode("<hr/>",$str);
		elseif (strpos($str,"[mas]")!==false)
			list($excerpt,$rest)=explode("[mas]",$str);
		else
			$excerpt=$str;

		$excerpt=strip_tags($excerpt);
		if (strlen($excerpt)>$length) {
			$excerpt=substr($excerpt,0,$length-3).$etc;
		}
		$excerpt=preg_replace('~&([a-z]{1,2})(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|orn|th);~i', '$1', htmlentities($excerpt) );//;
		return $excerpt; //self::makePlainString($excerpt);
	}

	public static function getMonthName($mes,$corto=true) {
		$mes=intval($mes);
		$mes=substr("00$mes",-2);
		$meses=array(
			"01"=>"Enero",
			"02"=>"Febrero",
			"03"=>"Marzo",
			"04"=>"Abril",
			"05"=>"Mayo",
			"06"=>"Junio",
			"07"=>"Julio",
			"08"=>"Agosto",
			"09"=>"Setiembre",
			"10"=>"Octubre",
			"11"=>"Noviembre",
			"12"=>"Diciembre",
		);

		$nombre=$meses[$mes];
		if ($corto) $nombre=substr($nombre,0,3);
		return $nombre;
	}

	public static function stringOccurrence($needle,$haystack) {
		$occurrences=0;
		$pos=strpos($haystack,$needle);
		while($pos!==false) {
			$occurrences++;
			$haystack=substr($haystack,$pos+1);
			$pos=strpos($haystack,$needle);
		}

		return $occurrences;
	}

	public static function removeTags($content) {

		$validBlocks=array();
		preg_match_all("~<(pre|code)([^>]*)>(.*)</\\1>~Usi",$content,$matches,PREG_SET_ORDER);
		foreach($matches as $idx=>$match) {
			$tagIdentifier="[[".$match[1]."_".($idx+1)."]]";
			$content=str_replace($match[0],$tagIdentifier,$content);
			$validBlocks[$idx]=$match;
//			dump_var($match);
		}
		preg_match_all("~<([a-z\-]*)([^>]*)>(.*)</\\1>~msi",$content,$matches,PREG_SET_ORDER);
		foreach($matches as $match) {
			$content=str_replace($match[0],"",$content);
		}

		preg_match_all("~<([a-z\-]*)>(.*)</\\1>~msi",$content,$matches,PREG_SET_ORDER);
		foreach($matches as $match) {
			$content=str_replace($match[0],"",$content);
		}
		preg_match_all("~<([a-z\-]*)([^>]*)>~msi",$content,$matches,PREG_SET_ORDER);
		foreach($matches as $match) {
			$content=str_replace($match[0],"",$content);
		}

		$content=trim($content);

		foreach($validBlocks as $idx => $match) {
			$validBlock=$match[0];
			preg_match_all("~<(pre|code)([^>]*)>(.*)</\\1>~si",$validBlock,$matches,PREG_SET_ORDER);
			$validBlock=str_replace($matches[0][3],htmlentities($matches[0][3]),$validBlock);
			$validBlock=str_replace("<{$matches[0][1]}{$matches[0][2]}>","<pre class=\"prettycode\">",$validBlock);
			$validBlock=str_replace("</{$matches[0][1]}>","</pre>",$validBlock);
			$tagIdentifier="[[".$match[1]."_".($idx+1)."]]";
			$content=str_replace($tagIdentifier,$validBlock,$content);
		}

		return $content;
	}
}