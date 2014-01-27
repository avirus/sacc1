<?php
//    SQUID Acconting                                            [SAcc system]
//	  Copyright (C) 2003-2010  Vyacheslav Nikitin 
//
//    This program is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program; if not, write to the Free Software
//    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

// $Author: slavik $ $Rev: 115 $
// $Id: auth.php 115 2013-05-29 15:41:35Z slavik $
// authenticators
/*
auth_cli - функция авторизации клиента.
вход: $user, $password
выход: true\false
*/
function auth_cli($user, $passwd, $mlink) {
	global $auth_mode;
	global $msg;
	$ares = flase;
	$passwd = stripslashes ( $passwd );
	$user = stripslashes ( $user );
	if ((! isset ( $user )) or (! isset ( $passwd )))
		return false;
	$user = rawurlencode ( $user );
	$passwd = rawurlencode ( $passwd );
	$passwd = str_replace ( "\\.", ".", $passwd );
	$user = str_replace ( "\\.", ".", $user );
	switch ($auth_mode) {
		case 2 :
			{
				// AD mode
				$ares = auth_smb ( $user, $passwd );
				break;
			}
		case 1 :
			{
				// NCSA mode
				$ares = auth_ncsa ( $user, $passwd );
				break;
			}
		case 3 :
			{
				// MySQL mode
				$ares = auth_mysql ( $user, $passwd, $mlink );
				break;
			}
		default :
			{
				//WTF?!
				echo "incorrect auth mode!";
				die ( 0 );
			}
	}
	$msg = $e . " mode " . $auth_mode . "/" . $ares . "/" . $user . "/" . $passwd;
	//$msg2=$ares;
	if ($ares == "ERR")
		return false;
	else if (strstr ( $ares, "OK" ) != FALSE)
		return true;
	return false;
}

//      Функция smb_auth($user, $passwd) - авторизует пользователя с заданным
//                                              логином и паролем
//      Вход :
//             $user - логин (ник) пользователя)
//             $passwd - пароль пользователя
//      Выход: в случае успешного прохождения аутентификации возвращается true, иначе - false
//      Примечание 1: для проверки логина и пароля используется внешняя программа
//                    возвращающая один из двух ответов: "OK" или "ERR"
//
//
function auth_ncsa($user, $passwd) {
	global $bin_prefix;
	global $ncsa_passwd;
	$e = "echo \"" . $user . " " . $passwd . "\" | $bin_prefix/ncsa_auth $ncsa_passwd";
	//         $e = escapeshellcmd($checkstring);
	//$e=$checkstring;
	$result = shell_exec ( $e );
	return $result;
}

function auth_smb($user, $passwd) {
	global $bin_prefix;
	global $domain;
	//global $dc_ip;
	$e = "echo \"" . $user . " " . $passwd . "\" | $bin_prefix/smb_auth -W $domain";
	//         $e = escapeshellcmd($checkstring);
	//$e=$checkstring;
	$result = shell_exec ( $e );
	return $result;
}

function auth_mysql($user, $passwd, $mlink) {
	//$mlink = db_connect ();
	$result = mysql_query ( "select * from users where login = '".mysql_real_escape_string($user)."' and passwd='$passwd' and perm=777", $mlink );
	echo mysql_error ();
	$dat1 = "select * from admins where login = '".mysql_real_escape_string($user)."' and passwd='" . md5 ( $passwd ) . "'";
	if (mysql_num_rows ( $result ) == 0) {
		//mysql_close ( $mlink );
		return "ERR";
	} else if (mysql_num_rows ( $result ) != 0) {
		//mysql_close ( $mlink );
		return "OK";
	}
	//@mysql_close ( $mlink );
	return "ERR";
}

//      Функция auth_adm($user, $passwd) - авторизует пользователя с заданным
//                                              логином и паролем
//      Вход :
//             $user - логин (ник) пользователя)
//             $passwd - пароль пользователя
//      Выход: в случае успешного прохождения аутентификации возвращается true, иначе - false
function auth_adm($user, $passwd, $mlink) {
	global $dat1;
	
	if ((! isset ( $user )) or (! isset ( $passwd )))
		return false;
	else {
		//$mlink = db_connect ();
		$dat1 = "select * from admins where login = '".mysql_real_escape_string($user)."' and passwd = '" . md5 ( $passwd ) . "'";
		$result = mysql_query ( $dat1, $mlink );
		echo mysql_error ();
		if (mysql_num_rows ( $result ) == 0) {
			//mysql_close ( $mlink );
			return false;
		} else if (mysql_num_rows ( $result ) != 0) {
			//mysql_close ( $mlink );
			return true;
		}
	}
	//@mysql_close ( $mlink );
	return false;
}

?>