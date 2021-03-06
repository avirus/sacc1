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
// $Id: functions.php 115 2013-05-29 15:41:35Z slavik $
// registerglobals workaround
if (! get_cfg_var ( "register_globals" )) {
	extract ( $_REQUEST, EXTR_SKIP );
	extract ( $_SERVER, EXTR_OVERWRITE );
}
//Content-Type: text/css; charset=windows-1251
header ( "Content-type:text/html; charset=koi8-r" );
//
//      ������� dotize($num) - ����������� ����� ����� ������ ��� ������� � ����� $num,
//                             ������� � ������ �������� �������
//      ���� : $num - ����� ��� ��������������
//      �����: ������������ ��������������� �����
//
// ����������: ������� Greyder-� �� ������� �������. ;)
//
function dotize($num) {
	global $delimiter;
	$num = strrev ( preg_replace ( "/(\d{3})/", "\\1" . $delimiter, strrev ( $num ) ) );
	return $num;

}

//	������� option($name, $link)
//      ����:
//		$name ��� �����
//		$link �� ������� � ����
//	�����: �������� �����
function option($name, $link) {
	$result = mysql_query ( "SELECT value from options where name='".mysql_real_escape_string($name)."'", $link );
	$res = @mysql_result ( $result, 0, "value" );
	@mysql_free_result ( $result );
	return $res;
}

function logevent($message) {
	global $mysql_server, $mysql_login, $mysql_passwd, $mysql_database, $PHP_AUTH_USER;
	
	$loglink = db_connect ();
	$message = date ( "d.m.Y H:i:s" ) . " $PHP_AUTH_USER " . $_SERVER ['REMOTE_ADDR'] . " " . addslashes ( $message );
	mysql_query ( "INSERT INTO syslog (record) VALUES('".mysql_real_escape_string($message)."');", $loglink );
	mysql_close ( $loglink );
}

//      ---- � ��� ����� ����� ��� �� ��������, � ��������� ���� ��, �� ����.
//      ������� get_month_year() - ���������� ������� ����� � ���
//      ���� : ������
//      �����: ������� ���������� ������, ������� ������� �������� -
//             �������� ������ (��-������), ������ ������� - ���
//
function get_month_year() {
	$date = getdate ();
	return array (date ( "F" ), $date [year] );
}

// error handling routine
function debug() {
	$debug_array = debug_backtrace ();
	$counter = count ( $debug_array );
	for($tmp_counter = 0; $tmp_counter != $counter; ++ $tmp_counter) {
		?>
<table width="558" height="116" border="1" cellpadding="0"
	cellspacing="0" bordercolor="#000000">
	<tr>
		<td height="38" bgcolor="#D6D7FC"><font color="#000000">function <font
			color="#FF3300"><?php
		echo ($debug_array [$tmp_counter] ["function"]);
		?>(</font> <font
			color="#2020F0"><?php
		//count how many args a there
		$args_counter = count ( $debug_array [$tmp_counter] ["args"] );
		//print them
		for($tmp_args_counter = 0; $tmp_args_counter != $args_counter; ++ $tmp_args_counter) {
			echo ($debug_array [$tmp_counter] ["args"] [$tmp_args_counter]);
			
			if (($tmp_args_counter + 1) != $args_counter) {
				echo (", ");
			} else {
				echo (" ");
			}
		}
		?></font><font color="#FF3300">)</font></font></td>
	</tr>
	<tr>
		<td bgcolor="#5F72FA"><font color="#FFFFFF">{</font><br>
		<font color="#FFFFFF">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;file: <?php
		echo ($debug_array [$tmp_counter] ["file"]);
		?></font><br>
		<font color="#FFFFFF">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;line: <?php
		echo ($debug_array [$tmp_counter] ["line"]);
		?></font><br>
		<font color="#FFFFFF">}</font></td>
	</tr>
</table>
<?php
		if (($tmp_counter + 1) != $counter) {
			echo ("<br>was called by:<br>");
		}
	}
	exit ();
}

//
//      ������� show_info($id) - ��� ��������� id'a ������ ������� �
//                               ������� (���� ������) � ������������
//                               ���������� ������ � HTML-�������
//      ���� : $id - username
//      �����: ���� ��� ������ - true, ����� - ��� ������
//      ����������: ���� ��������� �� ������� ��������� ������ � ����
//                    ������, ������������ ��� ������ 3
//
function show_info($link, $id) {
	global $megabyte_cost;
	global $lang;
	if ($lang == 0) {
		include "../inc/ru.php";
	}
	;
	if ($lang == 1) {
		include "../inc/en.php";
	}
	;
	
	$res = mysql_query ( "SELECT u.login as login, u.quota as quota, u.used as used, u.email as email, u.descr as descr, a.vname as aid FROM users u, acl a WHERE u.id=".(int)$id." and u.aid=a.id", $link );
	if (! $res) {
		echo mysql_error ();
		return 3;
	}
	$i = 0;
	if (0 == mysql_numrows ( $res )) {
		echo "error: $web_client_nouser";
		return false;
	}
	
	$nick = mysql_result ( $res, $i, "login" );
	$lim = mysql_result ( $res, $i, "quota" );
	$cur = mysql_result ( $res, $i, "used" );
	$email = mysql_result ( $res, $i, "email" );
	$descr = mysql_result ( $res, $i, "descr" );
	$timeacl = mysql_result ( $res, $i, "aid" );
	$rcpt = "rcpt='" . str_replace ( ",", "' OR rcpt='", $email ) . "'";
	$res = mysql_query ( "SELECT SUM(size) FROM mail WHERE $rcpt", $link );
	$msum = @mysql_result ( $res, 0 );
	$msum = dotize ( $msum );
	$cur = $cur - 1;
	if ($megabyte_cost > 0) {
		echo "$web_client_mbcost $megabyte_cost";
	}
	;
	echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0>
<TR>
<TD><B>$view_user</B></TD>
<TD>&nbsp;$descr</TD>
</TR>
<TR>
<TD><B>$view_login</B></TD>
<TD>&nbsp;$nick</TD>
</TR>
<TR><TD><B>$view_email</B></TD>
<TD>&nbsp;$email</TD></TR>";
	echo "</TABLE><BR><TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0>";
	if ($megabyte_cost > 0) {
		echo "<TR><TD><B>$web_client_account_total</B></TD><TD ALIGN=RIGHT>&nbsp;";
		if ($lim != "0") {
			echo " " . dotize ( ( int ) (($megabyte_cost * ( int ) ($lim - $cur)) / (1024 * 1024)) ) . " ";
		} else {
			echo "$web_client_unlim";
		}
		echo "</TD>
<TD>&nbsp;���.</TD>
</TR>";
	}
	;
	echo "<TR><TD><B>�����:</B></TD>
<TD ALIGN=RIGHT>&nbsp;";
	if ($lim != "0") {
		echo " " . dotize ( $lim ) . " ";
	} else {
		echo "$web_client_unlim";
	}
	echo "</TD>
<TD>&nbsp;$word_byte</TD>
</TR>
<TR>
<TD><B>$web_client_used</B></TD>
<TD ALIGN=RIGHT>&nbsp;" . dotize ( $cur ) . "</TD>";
	if (( int ) ($msum) > 0) {
		echo "<TD>&nbsp;$word_byte [$web_client_in_mail_trf $msum" . " $word_byte ]</TD>";
	}
	;
	echo "</TR><TR>
<TD><B>�������� (����):</B></TD>
<TD ALIGN=RIGHT>&nbsp;";
	if ($lim != "0") {
		echo " " . dotize ( ( int ) ($lim - $cur) ) . " ";
	} else {
		echo "$web_client_unlim";
	}
	echo "</TD>
<TD>&nbsp;$word_byte</TD>
</TR>
<TR>
<TD><B>$work_time</B></TD>
<TD COLSPAN=2>&nbsp;$timeacl<TD>&nbsp;</TD>
</TR>
</TABLE>
";
	@mysql_free_result ( $res );
	return true;

}

//
//      ������� show_stat($id) - ���������� � ������� traf (���� ������ squid),
//                               �������� ������ ���������� ��� ��������� ������
//                               � ������������ �� � HTML-�������
//      ���� : $user - login
//      �����: ���� ��� ������ - true, ����� - ��� ������
//      ���������� 3: ���� ��������� �� ������� ����������� � ���� ������,
//                    ������������ ��� ������ 2
//      ���������� 4: ���� ��������� �� ������� ��������� ������ � ����
//                    ������, ������������ ��� ������ 3
//
function show_stat($link, $id) {
	//BUG! date_default_timezone_get 
	date_default_timezone_set ( 'Asia/Yekaterinburg' );
	global $o;
	global $detailed;
	global $mode;
	global $lang;
	if ($lang == 0) {
		include "../inc/ru.php";
	}
	;
	if ($lang == 1) {
		include "../inc/en.php";
	}
	;
	
	switch ($o) {
		case "sizeD" :
			{
				$orderby = "size DESC";
				$o1 = "?o=sizeA";
				$o2 = "?o=siteA";
				$b1 = "#93BEE2";
				$b2 = "#FFFFFF";
				$p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
				$p2 = "";
				break;
			}
		case "sizeA" :
			{
				$orderby = "size ASC";
				$o1 = "?o=sizeD";
				$o2 = "?o=siteA";
				$b1 = "#93BEE2";
				$b2 = "#FFFFFF";
				$p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
				$p2 = "";
				break;
			}
		case "siteD" :
			{
				$orderby = "site DESC";
				$o1 = "?o=sizeA";
				$o2 = "?o=siteA";
				$b1 = "#FFFFFF";
				$b2 = "#93BEE2";
				$p1 = "";
				$p2 = "<A HREF=\"index.php$o2\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
				break;
			}
		case "siteA" :
			{
				$orderby = "site ASC";
				$o1 = "?o=sizeA";
				$o2 = "?o=siteD";
				$b1 = "#FFFFFF";
				$b2 = "#93BEE2";
				$p1 = "";
				$p2 = "<A HREF=\"index.php$o2\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
				break;
			}
		default :
			{
				$orderby = "size DESC";
				$o1 = "?o=sizeA";
				$o2 = "?o=siteA";
				$b1 = "#93BEE2";
				$b2 = "#FFFFFF";
				$p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
				$p2 = "";
			}
	}
	
	echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0>
<TR>
<TD BGCOLOR=#93BEE2
><TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1>
<TR>
<TD ALIGN=CENTER BGCOLOR=$b1>$p1<B><A HREF=\"index.php$o1&type=sites&id=$id\" STYLE=\"color:#000000; text-decoration: underline\">$web_client_downloaded</A></B></TD>
<TD ALIGN=CENTER BGCOLOR=$b2>$p2<B><A HREF=\"index.php$o2&type=sites&id=$id\" STYLE=\"color:#000000; text-decoration: underline\">$web_client_site</A></B></TD>
<TD ALIGN=CENTER BGCOLOR=#bfbfbf><B>$web_client_futime</B></TD>
<TD ALIGN=CENTER BGCOLOR=#bfbfbf><B>$web_client_lutime</B></TD>
</TR>
";
	$res = mysql_query ( "SELECT * FROM site WHERE u_id=".(int)$id." ORDER BY $orderby", $link );
	if (! $res)
		return 3;
	for($i = 0; $i < MYSQL_NUMROWS ( $res ); $i ++) {
		$site = mysql_result ( $res, $i, "site" );
		$size = mysql_result ( $res, $i, "size" );
		$lutime = mysql_result ( $res, $i, "lutime" );
		$futime = mysql_result ( $res, $i, "futime" );
		
		echo "<TR>
<TD BGCOLOR=#FFF7E5 ALIGN=RIGHT>" . dotize ( $size ) . "</TD>
<TD BGCOLOR=#FFF7E5>";
		if ($detailed == 1)
			if ($mode == "admin") {
				echo "<a href=\"index.php?id=$id&type=detail&site=$site\">";
			} else {
				echo "<a href=\"index.php?type=detail&site=$site\">";
			}
		;
		echo "$site";
		if ($detailed == 1) {
			echo "</a>";
		}
		;
		echo "<TD BGCOLOR=#FFF7E5>" . strftime ( "%B %d, %T", ( int ) $futime ) . "</TD>";
		echo "<TD BGCOLOR=#FFF7E5>" . strftime ( "%B %d, %T", ( int ) $lutime ) . "</TD>";
		echo "</TD></TR>";
	}
	echo "</TABLE></TD>
</TR>
</TABLE>
";
	$res = mysql_query ( "SELECT SUM(size) FROM site WHERE u_id=".(int)$id, $link );
	$sum = mysql_result ( $res, 0 );
	$res = mysql_query ( "SELECT count(site) FROM site WHERE u_id=".(int)$id, $link );
	$sites = mysql_result ( $res, 0 );
	mysql_close ();
	echo "<BR><SPAN CLASS=\"smalltext\"><B>$word_total:</B> " . dotize ( $sum ) . " $word_byte $web_client_downloaded_from $sites $web_client_sites_wrd.</SPAN>";
	return true;
}

//
//    ������� show_tail() - ������� ����� HTML-�������� ;)
//    ���� : ������
//    �����: ������ true
//
function show_tail() {
	global $version;
	include ("timer_show.php");
}

//
//      ������� show_mailstat($link, $login) - ���������� � ������� mail
//                               �������� ������ ���������� ��� ��������� login-a
//                               � ������������ �� � HTML-�������
//      ���� : $link - ���������� ���� ������
//             $login
//      �����: ���� ��� ������ - true, ����� - ��� ������
//      ���������� 1: ���� ��������� �� ������� ����������� � ���� ������,
//                    ������������ ��� ������ 2
//      ���������� 2: ���� ��������� �� ������� ��������� ������ � ����
//                    ������, ������������ ��� ������ 3
//      ���������� 3: ������� �ޣ�� ����� ���� �� ���� � ���������.
//
function show_mailstat($link, $login) {
	global $lang;
	if ($lang == 0) {
		include "../inc/ru.php";
	}
	;
	if ($lang == 1) {
		include "../inc/en.php";
	}
	;
	
	$result = mysql_query ( "SELECT email FROM traf WHERE login='".mysql_real_escape_string($login)."'", $link );
	if (mysql_numrows ( $result ) == 1) {
		$email = mysql_result ( $result, 0, "email" );
		$rcpt = "rcpt='" . str_replace ( ",", "' OR rcpt='", $email ) . "'";
		$result = mysql_query ( "SELECT size, frm FROM mail WHERE $rcpt ORDER BY size DESC", $link );
	}
	if (mysql_numrows ( $result ) > 0) {
		
		echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0>
<TR>
<TD BGCOLOR=#93BEE2><TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1><TR>
<TD ALIGN=CENTER BGCOLOR=#FFF7E5><B><SPAN STYLE=\"color:#000000\">$web_client_inmail</SPAN></TD>
<TD ALIGN=CENTER BGCOLOR=#FFF7E5><B><SPAN STYLE=\"color:#000000\">$web_client_mailfrom</SPAN></B></TD>
</TR>
";
		
		if (! $result)
			return 3;
		for($i = 0; $i < mysql_numrows ( $result ); $i ++) {
			$site = mysql_result ( $result, $i, "frm" );
			$size = mysql_result ( $result, $i, "size" );
			echo "<TR>
<TD BGCOLOR=#FFF7E5 ALIGN=RIGHT>" . dotize ( $size ) . "</TD>
<TD BGCOLOR=#FFF7E5>$site</TD>
</TR>
";
		}
		echo "</TABLE></TD>
</TR>
</TABLE>
";
		$result = mysql_query ( "SELECT SUM( size ) FROM mail WHERE $rcpt", $link );
		$sum = mysql_result ( $result, 0 );
		echo "<BR>
<SPAN CLASS=\"smalltext\"><B>$word_total:</B> " . dotize ( $sum ) . " $word_byte .</SPAN>
";
	}
	;
	return true;

}

//
//    ������� show_head() - ������� ����� HTML-��������
//    ���� : ������
//    �����: ������ true
//
function show_head() {
	global $version;
	global $mod_name, $origin;
	
	echo "<HTML>
<HEAD>
<META HTTP-EQUIV=\"Expires\" CONTENT=\"0\">
<META HTTP-EQUIV=\"Pragma\"  CONTENT=\"no-cache\">
<META HTTP-EQUIV=\"Cache-Control\" CONTENT=\"no-cache\">
<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=koi8-r\">";
	if (isset ( $origin )) {
		echo "<TITLE>$origin: $mod_name</TITLE>";
	} else {
		echo "<TITLE>SAcc v. $version: $mod_name</TITLE>";
	}
	;
	echo "<LINK REL=STYLESHEET TYPE=\"text/css\" HREF=../1.css>
</HEAD>
<BODY BGCOLOR=#FFFFFF TEXT=#000000>
";
	return true;

}

//
//    ������� show_error() - ������� ����������� ��������� �� ������
//    ���� : ������
//    �����: ������ true
//
function show_error() {
	global $word_warning, $web_client_noaccess;
	echo "<H1>$word_warning</H1><P>
        <FONT COLOR=#FF0000>$web_client_noaccess</FONT></P>";
	//<FORM ACTION=index.php METHOD=get>
	//<INPUT TYPE=submit CLASS=inputsubmit NAME=try VALUE=\"����������� ��� ���\">
	//</FORM>";
	return true;
}

//
//      ������� show_detail($id, $site) - ���������� � ������� detail (���� ������ trf),
//                               �������� ������ ���������� ��� ��������� ������ � �����
//                               � ������������ �� � HTML-�������
//      ���� : $id - login
//      �����: ���� ��� ������ - true, ����� - ��� ������
//      ���������� 3: ���� ��������� �� ������� ����������� � ���� ������,
//                    ������������ ��� ������ 2
//      ���������� 4: ���� ��������� �� ������� ��������� ������ � ����
//                    ������, ������������ ��� ������ 3
//
function show_detail($link, $id, $site) {
	global $o;
	global $mode;
	global $lang;
	global $settings;
	
	$page = ( int ) $_GET ['page'];
	// ----------------------------
	$pagestep = $settings ['pagelen'];
	//-----------------------------
	if ("" == $site) {
		echo "������ ����� �� ����� ���� ������!";
		return 1;
	}
	;
	$cquery = $_SERVER ['PHP_SELF'] . "?" . $_SERVER ['QUERY_STRING'];
	if (0 < strpos ( $cquery, "&page=" )) {
		$cquery = substr ( $cquery, 0, strpos ( $cquery, "&page=" ) );
	}
	;
	echo "<br><center><p style=\"font-size: 12\">";
	$result = mysql_query ( "SELECT count(utime) as rec FROM detail WHERE u_id='".(int)$id."' and url like('%" . mysql_real_escape_string($site) . "%')", $link );
	$pages = floor ( mysql_result ( $result, 0, "rec" ) / $pagestep );
	
	if ($page != 0)
		echo "<a href=\"$cquery" . "&page=0\"><<</a> ";
	if ($page > 10)
		echo "<a href=\"$cquery&page=" . ($page - 10) . "\"><</a> ";
	if ($page - 5 > 0) {
		for($i = $page - 5; $i < $page; $i ++) {
			echo "<a href=\"$cquery&page=$i\">$i</a> ";
		}
	} else {
		for($i = 1; $i < $page; $i ++) {
			echo "<a href=\"$cquery&page=$i\">$i</a> ";
		}
	}
	echo "<b>$page</b> ";
	if ($page + 5 < $pages) {
		for($i = $page + 1; $i < $page + 6; $i ++) {
			echo "<a href=\"$cquery&page=$i\">$i</a> ";
		}
	} else {
		for($i = $page + 1; $i < $pages; $i ++) {
			echo "<a href=\"$cquery&page=$i\">$i</a> ";
		}
	}
	
	if (($page + 10) < $pages) {
		echo "<a href=\"$cquery&page=" . ($page + 10) . "\">></a> ";
	}
	if ($page < $pages) {
		echo "<a href=\"$cquery&page=" . $pages . "\">>></a> ";
	}
	
	echo "</p>";
	
	if ($mode == "admin") {
		$query = "&type=detail&id=" . $_GET ['id'] . "&site=$site";
	} else {
		$query = "&type=detail&site=$site";
	}
	;
	switch ($o) {
		case "sizeD" :
			{
				$orderby = "size DESC";
				$o1 = "?o=sizeA";
				$o2 = "?o=timeA";
				$b1 = "#93BEE2";
				$b2 = "#FFFFFF";
				$p1 = "<A HREF=\"index.php" . $o1 . $query . "\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
				$p2 = "";
				break;
			}
		case "sizeA" :
			{
				$orderby = "size ASC";
				$o1 = "?o=sizeD";
				$o2 = "?o=timeA";
				$b1 = "#93BEE2";
				$b2 = "#FFFFFF";
				$p1 = "<A HREF=\"index.php" . $o1 . $query . "\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
				$p2 = "";
				break;
			}
		case "timeD" :
			{
				$orderby = "utime DESC";
				$o1 = "?o=sizeA";
				$o2 = "?o=timeA";
				$b1 = "#FFFFFF";
				$b2 = "#93BEE2";
				$p1 = "";
				$p2 = "<A HREF=\"index.php" . $o2 . $query . "\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
				break;
			}
		case "timeA" :
			{
				$orderby = "utime ASC";
				$o1 = "?o=sizeA";
				$o2 = "?o=timeD";
				$b1 = "#FFFFFF";
				$b2 = "#93BEE2";
				$p1 = "";
				$p2 = "<A HREF=\"index.php" . $o2 . $query . "\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
				break;
			}
		default :
			{
				$orderby = "size DESC";
				$o1 = "?o=sizeA";
				$o2 = "?o=timeA";
				$b1 = "#93BEE2";
				$b2 = "#FFFFFF";
				$p1 = "<A HREF=\"index.php" . $o1 . $query . "\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
				$p2 = "";
			}
	}
	
	if ($_COOKIE ['lang'] == 0) {
		require_once "../inc/ru.php";
	}
	;
	if ($_COOKIE ['lang'] == 1) {
		require_once "../inc/en.php";
	}
	;
	
	echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR>
<TD BGCOLOR=#93BEE2><TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1><TR>
<TD ALIGN=CENTER BGCOLOR=$b2>$p2<B><A HREF=\"index.php$o2$query\" STYLE=\"color:#000000; text-decoration: underline\">$word_time</A></B></TD>
<TD ALIGN=CENTER BGCOLOR=$b1>$p1<B><A HREF=\"index.php$o1$query\" STYLE=\"color:#000000; text-decoration: underline\">$word_byte</A></B></TD>
<TD ALIGN=LEFT BGCOLOR=#FFFFFF><b>$web_client_query</B></TD>
<TD ALIGN=RIGHT BGCOLOR=#FFFFFF><b>msec</B></TD>
<TD ALIGN=LEFT BGCOLOR=#FFFFFF><b>$web_client_src_ip</B></TD>
<TD ALIGN=LEFT BGCOLOR=#FFFFFF><b>$web_client_server_rply</B></TD></TR>";
	
	$res = mysql_query ( "SELECT utime, qtime, ip_addr, code, size, url FROM detail WHERE u_id='".(int)$id."' and url like('%" . mysql_real_escape_string($site) . "%')  ORDER BY $orderby limit " . ($page * $pagestep) . ",$pagestep", $link );
	//    if ( !$res )
	//        return 3;
	for($i = 0; $i < MYSQL_NUMROWS ( $res ); $i ++) {
		$utime = mysql_result ( $res, $i, "utime" );
		$size = mysql_result ( $res, $i, "size" );
		$qtime = mysql_result ( $res, $i, "qtime" );
		$ip_addr = long2ip ( mysql_result ( $res, $i, "ip_addr" ) );
		$code = mysql_result ( $res, $i, "code" );
		$url = mysql_result ( $res, $i, "url" );
		
		echo "<TR>
<TD BGCOLOR=#FFF7E5>" . strftime ( "%B %d, %T", ( int ) $utime ) . "</TD>
<TD BGCOLOR=#FFF7E5 ALIGN=RIGHT>" . dotize ( $size ) . "</TD>
<TD BGCOLOR=#FFF7E5 ALIGN=LEFT>$url</TD>
<TD BGCOLOR=#FFF7E5 ALIGN=RIGHT>$qtime</TD>
<TD BGCOLOR=#FFF7E5 ALIGN=RIGHT>$ip_addr</TD>
<TD BGCOLOR=#FFF7E5 ALIGN=LEFT>$code</TD>
</TR>
";
	}
	echo "</TABLE></TD>
</TR>
</TABLE>
";
	$res = mysql_query ( "SELECT SUM(size) FROM detail WHERE u_id=".(int)$id." and url like('%" . mysql_real_escape_string ( $site ) . "%')", $link );
	$sum = mysql_result ( $res, 0 );
	$res = mysql_query ( "SELECT count(utime) FROM detail WHERE u_id=".(int)$id." and url like('%" . mysql_real_escape_string ( $site ) . "%')", $link );
	$sites = mysql_result ( $res, 0 );
	mysql_close ();
	echo "<BR>
<SPAN CLASS=\"smalltext\"><B>$word_total:</B> " . dotize ( $sum ) . " $word_byte $web_client_downloaded_with " . dotize ( $sites ) . " $web_client_query.</SPAN>
";
	return true;

}

//      // NOT IMPLEMENTED NOW //
//      ������� show_acl() - ���������� � ������� acl (���� ������ sacc),
//                               �������� ������ ����������
//                               � ������������ �� � HTML-�������
//      ���� : $link - MySQL database connection descriptor
//      �����: ���� ��� ������ - true, ����� - ��� ������
//      ���������� 3: ���� ��������� �� ������� ����������� � ���� ������,
//                    ������������ ��� ������ 2
//      ���������� 4: ���� ��������� �� ������� ��������� ������ � ����
//                    ������, ������������ ��� ������ 3
//
function show_acl($link) {
	global $o, $word_total, $web_admin_description, $web_admin_name;
	
	switch ($o) {
		case "nameD" :
			{
				$orderby = "name DESC";
				$o1 = "?o=nameA";
				$o2 = "?o=descA";
				$b1 = "#93BEE2";
				$b2 = "#FFFFFF";
				$p1 = "<A HREF=\"index.php" . $o1 . "&type=acl\"><IMG ALT=\"������������� � �������� �������\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
				$p2 = "";
				break;
			}
		case "nameA" :
			{
				$orderby = "name ASC";
				$o1 = "?o=nameD";
				$o2 = "?o=descA";
				$b1 = "#93BEE2";
				$b2 = "#FFFFFF";
				$p1 = "<A HREF=\"index.php" . $o1 . "&type=acl\"><IMG ALT=\"������������� � ������ �������\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
				$p2 = "";
				break;
			}
		case "descD" :
			{
				$orderby = "vname DESC";
				$o1 = "?o=nameA";
				$o2 = "?o=descA";
				$b1 = "#FFFFFF";
				$b2 = "#93BEE2";
				$p1 = "";
				$p2 = "<A HREF=\"index.php" . $o2 . "&type=acl\"><IMG ALT=\"������������� � �������� �������\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
				break;
			}
		case "descA" :
			{
				$orderby = "vname ASC";
				$o1 = "?o=nameA";
				$o2 = "?o=descD";
				$b1 = "#FFFFFF";
				$b2 = "#93BEE2";
				$p1 = "";
				$p2 = "<A HREF=\"index.php" . $o2 . "&type=acl\"><IMG ALT=\"������������� � ������ �������\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
				break;
			}
		default :
			{
				$orderby = "id ASC";
				$o1 = "?o=nameA";
				$o2 = "?o=descD";
				$b1 = "#FFFFFF";
				$b2 = "#93BEE2";
				$p1 = "<A HREF=\"index.php" . $o2 . "&type=acl\"><IMG ALT=\"������������� � ������ �������\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
				$p2 = "";
			}
	}
	
	echo "<a href=\"index.php?type=acled&id=0\">add new acl</a>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR>
<TD BGCOLOR=#93BEE2><TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1><TR>
</tr>
<tr>
<TD ALIGN=CENTER BGCOLOR=$b2>$p2<B><A HREF=\"index.php$o2\" STYLE=\"color:#000000; text-decoration: underline\">$web_admin_description</A></B></TD>
<TD ALIGN=CENTER BGCOLOR=$b1>$p1<B><A HREF=\"index.php$o1\" STYLE=\"color:#000000; text-decoration: underline\">$web_admin_name</A></B></TD>";
	$res = mysql_query ( "SELECT sysname, vname, id FROM acl ORDER BY $orderby", $link );
	if (! $res)
		return 3;
	for($i = 0; $i < MYSQL_NUMROWS ( $res ); $i ++) {
		$descr = mysql_result ( $res, $i, "vname" );
		$name = mysql_result ( $res, $i, "sysname" );
		$id = mysql_result ( $res, $i, "id" );
		echo "<TR>
<TD BGCOLOR=#FFF7E5 ALIGN=LEFT><a href=\"index.php?type=acled&id=$id\">" . $descr . "</a></TD>
<TD BGCOLOR=#FFF7E5 ALIGN=LEFT>" . $name . "</TD>
</TR>
";
	}
echo "<TD BGCOLOR=#FFF7E5 ALIGN=LEFT>$word_total</TD><TD BGCOLOR=#FFF7E5 ALIGN=LEFT>" . dotize ( $acl_count ) . "</TD></TR>";
	echo "</TABLE></TD>
</TR>
</TABLE>
";
	$res = mysql_query ( "SELECT count(id) FROM acl", $link );
	$acl_count = mysql_result ( $res, 0 );
	mysql_close ();
	//echo "<BR><SPAN CLASS=\"smalltext\"><B>�����:</B> " . dotize ( $acl_count ) . " ������.</SPAN>";
	return 0;
}
;

?>
