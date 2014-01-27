<?php
// $Author: slavik $ $Rev: 113 $
// $Id: functions.php 113 2012-11-20 11:42:29Z slavik $

//
//      Функция show_help() - отображает краткую справку по программе
//      Вход : ничего
//      Выход: всегда true;
//
function show_help() {
	global $version, $web_client_welcome, $web_client_heap_unauth;
	echo "<P><H1>Dear user</H1><P>
    $web_client_welcome
 $version</A>.
</P><P>$web_client_heap_unauth";
	return true;
}
?>