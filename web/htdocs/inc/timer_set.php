<?php
global $tstart, $settings, $link ;
// $Author: slavik $ $Rev: 113 $
// $Id: timer_set.php 113 2012-11-20 11:42:29Z slavik $

// get preferences
$link = db_connect ();
$result = mysql_query ( "SELECT name, value FROM options;", $link );
for($i = 0; $i < mysql_numrows ( $result ); $i ++) {
	$name = mysql_result ( $result, $i, "name" );
	$value = mysql_result ( $result, $i, "value" );
	$settings [$name] = $value;
}

if (isset ( $_COOKIE ['lang'] )) {
	$lang = $_COOKIE ['lang'];
} else {
	$lang = $settings ["language"];
};
if (0==$lang) {
	require_once "ru.php";
}
if (1==$lang) {
	require_once "en.php";
};
//Считываем текущее время 
$mtime = microtime ();
//Разделяем секунды и миллисекунды 
$mtime = explode ( " ", $mtime );
//Составляем одно число из секунд и миллисекунд 
$mtime = $mtime [1] + $mtime [0];
//Записываем стартовое время в переменную 
$tstart = $mtime;
unset ( $mtime );
?>