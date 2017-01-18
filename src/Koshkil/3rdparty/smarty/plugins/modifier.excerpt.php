<?php
use Koshkil\Framework\Support\StringUtils;

function smarty_modifier_excerpt($text, $length = 80, $etc = '...'){
	$text=StringUtils::makeExcerpt($text, $length, $etc);
	return $text;//iconv('utf-8', 'us-ascii//TRANSLIT', $text);
}