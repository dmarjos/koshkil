<?php
function smarty_modifier_longdate($date){
	$dt=explode(" ",$date);
	if (preg_match("~([0-9]{2})/([0-9]{2})/([0-9]{4})~si",$dt[0]))
		$dt[0]=implode("-",array_reverse(explode("/",$dt[0])));

	$ymd=explode("-",$dt[0]);

	$meses=array(
		"01"=>array("nombre"=>"Enero"),
		"02"=>array("nombre"=>"Febrero"),
		"03"=>array("nombre"=>"Marzo"),
		"04"=>array("nombre"=>"Abril"),
		"05"=>array("nombre"=>"Mayo"),
		"06"=>array("nombre"=>"Junio"),
		"07"=>array("nombre"=>"Julio"),
		"08"=>array("nombre"=>"Agosto"),
		"09"=>array("nombre"=>"Setiembre"),
		"10"=>array("nombre"=>"Octubre"),
		"11"=>array("nombre"=>"Noviembre"),
		"12"=>array("nombre"=>"Diciembre"),
	);

	$fecha=$meses[$ymd[1]]["nombre"]." ".$ymd[2].", ".$ymd[0];
	$fecha=$ymd[2]." de ".$meses[$ymd[1]]["nombre"]." de ".$ymd[0];
	return $fecha;
}
