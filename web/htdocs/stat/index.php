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
// $Id: index.php 115 2013-05-29 15:41:35Z slavik $
$mod_name = "$web_client_header";
global $tstart, $settings, $link;
require_once ("../inc/version.php");
require_once ("../inc/mysql.php");
require_once ("../inc/timer_set.php");
require_once ("../inc/functions.php");
require_once ("functions.php");
require_once ("../inc/auth.php");
$mode = "user";

if (! (isset ( $PHP_AUTH_USER ))) {
	Header ( "WWW-Authenticate: Basic realm=\"$web_client_auth_realm\"" );
	Header ( "HTTP/1.0 401 Unauthorized" );
	show_head ();
	show_help ();
	//            echo "$o 1. Введено: $PHP_AUTH_USER : $PHP_AUTH_PW <br>";
	//            echo "2. Введено: $ulogin : $passwd ";
	show_tail ();
	exit ();
} else //        if ( smb_auth($ulogin, $passwd) )
if (auth_cli ( $PHP_AUTH_USER, $PHP_AUTH_PW, $link )) 

//        if (true)
{
	// ------------ Просмотр запросов пользователя (событие "detail") ------------
	if (($type == "detail") and ($detailed == 1)) {
		show_head ();
		echo "<A HREF=\"index.php#$id\">&lt;&lt;</A>\n<BR>\n";
		$result = mysql_query ( "SELECT id FROM users where login='$PHP_AUTH_USER'", $link );
		$id = mysql_result ( $result, 0, "id" );
		$result = mysql_query ( "SELECT name, value FROM options;", $link );
		for($i = 0; $i < mysql_numrows ( $result ); $i ++) {
			$name = mysql_result ( $result, $i, "name" );
			$value = mysql_result ( $result, $i, "value" );
			$settings [$name] = $value;
		}
		show_info ( $link, $id );
		echo "<HR NOSHADE COLOR=#000000 SIZE=1>\n<BR>\n";
		show_detail ( $link, $id, $_GET ['site'] );
		show_tail ();
		@mysql_close ( $link );
		exit ();
	} // ------------ Просмотр обычной статистики ----------------------------------
else //if (!(isset($type))) 
	{
		show_head ();
		list ( $month, $year ) = get_month_year ();
		//            echo "1. Введено: $PHP_AUTH_USER : $PHP_AUTH_PW <br>";
		//             echo "2. Введено: $ulogin : $passwd ";
		echo "<H1>$web_client_your_stat $month $year</H1>\n";
		$result = mysql_query ( "SELECT id FROM users where login='".mysql_real_escape_string($PHP_AUTH_USER)."'", $link );
		$id = mysql_result ( $result, 0, "id" );
		show_info ( $link, $id );
		echo "<HR NOSHADE COLOR=#000000 SIZE=1>\n<BR>\n";
		show_stat ( $link, $id );
		echo "<HR NOSHADE COLOR=#000000 SIZE=1>\n";
		if (! isset ( $no_mail )) {
			show_mailstat ( $link, $PHP_AUTH_USER );
		}
		;
		echo "<HR NOSHADE COLOR=#000000 SIZE=1>\n";
		//                show_form();
		show_tail ();
		@mysql_close ( $link );
	}
} else {
	Header ( "WWW-Authenticate: Basic realm=\"$web_client_auth_realm\"" );
	Header ( "HTTP/1.0 401 Unauthorized" );
	show_head ();
	echo "1. Введено: $PHP_AUTH_USER : $PHP_AUTH_PW <br>";
	echo "$msg $msg2";
	show_help ();
	echo "<P><FONT COLOR=#FF0000>$web_client_auth_wrong</FONT></P>";
	show_tail ();
	@mysql_close ( $link );
	exit ();
}
?>