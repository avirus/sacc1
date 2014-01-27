<?php
//
//    SQUID Acconting                                            [SAcc system]
//	  Copyright (C) 2003-2010  Vyacheslav Nikitin 
//    Copyright (C) 2010  Yuri Dvinyaninov
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
//	$Id: advfunctions.php 115 2013-05-29 15:41:35Z slavik $
//
//      Функция show_adsearch($userid) - обращается к Active Directory,
//                               выбирает оттуда информацию для заданного логина или фамилии
//                               и представляет ее в HTML-формате
//      Вход : $userid - логин или фамилия
//      Выход: если все удачно - true, иначе - код ошибки
//
function show_adsearch($userid)
{
//$debug=0;
if (!function_exists("ldap_connect")) { echo "please add support for php LDAP.";return 1;};
if(strlen($userid) > 2){
//DN needs to be that of your organisation
    $dn= $settings ["addn"];
    //if($debug){print"userid='$userid'<br>";}
    $useridorig=$userid;
    $userid=iconv("koi8-r", "utf-8", $userid);

    $login = $settings ["adlogin"]; 
    $password =$settings ["adpwd"]; 
//We connect via the IP address, you may be able to use a server name here
	$adip=$settings ["adip"];
    $ad = ldap_connect($adip)
          or die("Couldn't connect to AD ($adip)!");
    ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);

//bind to the server using credentials supplied
    $bind = ldap_bind($ad,$login,$password) 
    	or die("Couldn't authenticate to AD ($adip) with ($login / $password)!");

//Find the entries that match the criteria
    $filter="(|(sAMAccountName=$userid*)(cn=$userid*))";
    $justthese = array("cn", "sAMAccountName", "mail", "objectclass");
    $result = ldap_search($ad,$dn,$filter,$justthese);
    $count = ldap_count_entries($ad,$result);
    //if($debug){print"count=$count<br>";}

if($count)echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR>
<TD BGCOLOR=#93BEE2><TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1><TR>
<TD ALIGN=LEFT BGCOLOR=#FFFFFF><b>User</B></TD>
<TD ALIGN=LEFT BGCOLOR=#FFFFFF><b>Login</B></TD></TR>";

$entry = ldap_first_entry( $ad, $result );
while( $entry ){
 $dn = ldap_get_dn( $ad, $entry );
 if($debug){echo "<b>" . iconv("utf-8", "koi8-r", $dn) . "</b><br>\n";}
 $attrs = ldap_get_attributes( $ad, $entry );
 echo"<TR>";
 for( $i=0; $i<$attrs['count']; $i++ ){
  //if($debug){echo "$attrs[$i]: ";}
  for( $j=0; $j<$attrs[$attrs[$i]]['count']; $j++ ){
    if($attrs[$i]=='cn'){$other=iconv("utf-8", "koi8-r", $attrs[$attrs[$i]][$j]);}
    if($attrs[$i]=='sAMAccountName'){$login=iconv("utf-8", "koi8-r", $attrs[$attrs[$i]][$j]);} 
  }
 }
 echo"<TD BGCOLOR=#FFF7E5 ALIGN=LEFT><A HREF=\"index.php?type=create&login=$login&other=$other&email=$login@prognoz.ru\">" . $other . "</A></TD>";
 echo"<TD BGCOLOR=#FFF7E5 ALIGN=LEFT><A HREF=\"index.php?type=create&login=$login&other=$other&email=$login@prognoz.ru\">" . $login . "</A></TD>";
 echo"</TR>";
 $entry = ldap_next_entry( $ad, $entry );
}
ldap_free_result( $result );
ldap_unbind($ad);

} else{echo"length must be > 2";} //first if chek strlen
echo "</TABLE></TD></TR></TABLE>";
}

//
//      Функция show_service_detail($id, $site) - обращается к таблице detail (база данных trf),
//                               выбирает оттуда информацию для заданного логина и сайта
//                               и представляет ее в HTML-формате
//      Вход : $id - login
//      Выход: если все удачно - true, иначе - код ошибки
//      Примечание 3: если программе не удается подключится к базе данных,
//                    возвращается код ошибки 2
//      Примечание 4: если программе не удается выполнить запрос к базе
//                    данных, возвращается код ошибки 3
//
function show_service_detail($link, $id, $site)
{ 
global $o;
global $mode;
global $lang;
global $settings;

$page=(int)$_GET['page'];
// ----------------------------
$pagestep=$settings['pagelen'];
//-----------------------------
if (""==$site) {echo "строка сайта не может быть пустой!";return 1;};
$cquery=$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'];
if (0<strpos($cquery,"&page=")) {$cquery=substr($cquery,0,strpos($cquery,"&page="));};
echo "<br><center><p style=\"font-size: 12\">";
$result = mysql_query("SELECT count(utime) as rec FROM detail WHERE u_id='".(int)$id."' and url like('%".mysql_real_escape_string($site)."%')", $link);
$pages= floor(mysql_result( $result, 0, "rec")/$pagestep);

if ($page!=0) echo "<a href=\"$cquery"."&page=0\"><<</a> ";
if ($page>10) echo "<a href=\"$cquery&page=".($page-10)."\"><</a> ";
if ($page-5>0) 
{
	for ($i = $page-5; $i < $page; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
else 
{
	for ($i = 1; $i < $page; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
echo "<b>$page</b> ";
if ($page+5<$pages) 
{
	for ($i = $page+1; $i < $page+6; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
else
{
	for ($i = $page+1; $i < $pages; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}

if (($page+10)<$pages) {echo "<a href=\"$cquery&page=".($page+10)."\">></a> ";}
if ($page<$pages) {echo "<a href=\"$cquery&page=".$pages."\">>></a> ";}

echo "</p>";




  if ($mode=="admin") 
    {$query="&type=detail&id=".$_GET['id']."&site=$site";}
  else
    {$query="&type=detail&site=$site";};
    switch ($o) {
        case "sizeD" : {
                        $orderby = "size DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=timeA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php".$o1.$query."\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "sizeA" : {
                        $orderby = "size ASC";
                        $o1 = "?o=sizeD";
                        $o2 = "?o=timeA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php".$o1.$query."\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "timeD" : {
                        $orderby = "utime DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=timeA";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php".$o2.$query."\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        break;
                       }
        case "timeA" : {
                        $orderby = "utime ASC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=timeD";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php".$o2.$query."\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        break;
                       }
             default : {
                        $orderby = "size DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=timeA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php".$o1.$query."\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        $p2 = "";
                       }
    }



if ($_COOKIE['lang']==0) {include "../inc/ru.php";};
if ($_COOKIE['lang']==1) {include "../inc/en.php";};

echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR>
<TD BGCOLOR=#93BEE2><TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1><TR>
<TD ALIGN=CENTER BGCOLOR=$b2>$p2<B><A HREF=\"index.php$o2$query\" STYLE=\"color:#000000; text-decoration: underline\">$word_time</A></B></TD>
<TD ALIGN=CENTER BGCOLOR=$b1>$p1<B><A HREF=\"index.php$o1$query\" STYLE=\"color:#000000; text-decoration: underline\">$word_byte</A></B></TD>
<TD ALIGN=LEFT BGCOLOR=#FFFFFF><b>$web_client_query</B></TD>
<TD ALIGN=RIGHT BGCOLOR=#FFFFFF><b>msec</B></TD>
<TD ALIGN=LEFT BGCOLOR=#FFFFFF><b>$web_client_src_ip</B></TD>
<TD ALIGN=LEFT BGCOLOR=#FFFFFF><b>$web_client_server_rply</B></TD></TR>";

    $res = mysql_query("SELECT utime, qtime, ip_addr, code, size, url FROM service_detail WHERE u_id='".(int)$id."' and site='".mysql_real_escape_string($site)."'  ORDER BY $orderby limit ".($page*$pagestep).",$pagestep", $link);
//    if ( !$res )
//        return 3;
    for ($i = 0; $i < MYSQL_NUMROWS($res); $i++)
        {
            $utime  = mysql_result($res,$i,"utime");
            $size  = mysql_result($res,$i,"size");
            $qtime  = mysql_result($res,$i,"qtime");
            $ip_addr = long2ip(mysql_result($res,$i,"ip_addr"));
            $code = mysql_result($res,$i,"code");
            $url = mysql_result($res,$i,"url");

            echo "<TR>
<TD BGCOLOR=#FFF7E5>".strftime ("%B %d, %T", (int)$utime)."</TD>
<TD BGCOLOR=#FFF7E5 ALIGN=RIGHT>" . dotize($size) . "</TD>
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
    $res = mysql_query("SELECT SUM(size) FROM service_detail WHERE u_id='".(int)$id."' and url like('%".mysql_real_escape_string($site)."%')", $link);
    $sum  = mysql_result($res,0);
    $res = mysql_query("SELECT count(utime) FROM service_detail WHERE u_id='".(int)$id."' and url like('%".mysql_real_escape_string($site)."%')", $link);
    $sites = mysql_result($res, 0); 
    mysql_close();
    echo "<BR>
<SPAN CLASS=\"smalltext\"><B>$word_total:</B> " . dotize($sum) . " $word_byte $web_client_downloaded_with ".dotize($sites)." $web_client_query.</SPAN>
";
    return true;
}

//
//      Функция show_all_service_detail($id, $site) - обращается к таблице detail (база данных trf),
//                               выбирает оттуда информацию для заданного логина и сайта
//                               и представляет ее в HTML-формате
//      Вход : $id - login
//      Выход: если все удачно - true, иначе - код ошибки
//      Примечание 3: если программе не удается подключится к базе данных,
//                    возвращается код ошибки 2
//      Примечание 4: если программе не удается выполнить запрос к базе
//                    данных, возвращается код ошибки 3
//
function show_all_service_detail($link, $site)
{ 
global $o;
global $mode;
global $lang;
global $settings;

$page=(int)$_GET['page'];
// ----------------------------
$pagestep=$settings['pagelen'];
//-----------------------------
if (""==$site) {echo "строка сайта не может быть пустой!";return 1;};
$cquery=$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'];
if (0<strpos($cquery,"&page=")) {$cquery=substr($cquery,0,strpos($cquery,"&page="));};
echo "<br><center><p style=\"font-size: 12\">";
//$result = mysql_query("SELECT count(utime) as rec FROM detail WHERE u_id='$id' and url like('%".mysql_escape_string($site)."%')", $link);
$result = mysql_query("SELECT count(utime) as rec FROM detail WHERE url like('%".mysql_real_escape_string($site)."%')", $link);
$pages= floor(mysql_result( $result, 0, "rec")/$pagestep);

if ($page!=0) echo "<a href=\"$cquery"."&page=0\"><<</a> ";
if ($page>10) echo "<a href=\"$cquery&page=".($page-10)."\"><</a> ";
if ($page-5>0) 
{
	for ($i = $page-5; $i < $page; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
else 
{
	for ($i = 1; $i < $page; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
echo "<b>$page</b> ";
if ($page+5<$pages) 
{
	for ($i = $page+1; $i < $page+6; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
else
{
	for ($i = $page+1; $i < $pages; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}

if (($page+10)<$pages) {echo "<a href=\"$cquery&page=".($page+10)."\">></a> ";}
if ($page<$pages) {echo "<a href=\"$cquery&page=".$pages."\">>></a> ";}

echo "</p>";




  if ($mode=="admin") 
    {$query="&type=detail&id=".$_GET['id']."&site=$site";}
  else
    {$query="&type=detail&site=$site";};
    switch ($o) {
        case "sizeD" : {
                        $orderby = "size DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=timeA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php".$o1.$query."\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "sizeA" : {
                        $orderby = "size ASC";
                        $o1 = "?o=sizeD";
                        $o2 = "?o=timeA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php".$o1.$query."\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "timeD" : {
                        $orderby = "utime DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=timeA";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php".$o2.$query."\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        break;
                       }
        case "timeA" : {
                        $orderby = "utime ASC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=timeD";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php".$o2.$query."\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        break;
                       }
             default : {
                        $orderby = "size DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=timeA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php".$o1.$query."\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        $p2 = "";
                       }
    }



if ($_COOKIE['lang']==0) {include "../inc/ru.php";};
if ($_COOKIE['lang']==1) {include "../inc/en.php";};

echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR>
<TD BGCOLOR=#93BEE2><TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1><TR>
<TD ALIGN=CENTER BGCOLOR=$b2>$p2<B><A HREF=\"index.php$o2$query\" STYLE=\"color:#000000; text-decoration: underline\">$word_time</A></B></TD>
<TD ALIGN=CENTER BGCOLOR=$b1>$p1<B><A HREF=\"index.php$o1$query\" STYLE=\"color:#000000; text-decoration: underline\">$word_byte</A></B></TD>
<TD ALIGN=LEFT BGCOLOR=#FFFFFF><b>$web_client_query</B></TD>
<TD ALIGN=RIGHT BGCOLOR=#FFFFFF><b>msec</B></TD>
<TD ALIGN=LEFT BGCOLOR=#FFFFFF><b>$web_client_src_ip</B></TD>
<TD ALIGN=LEFT BGCOLOR=#FFFFFF><b>$web_client_server_rply</B></TD></TR>";

    //$res = mysql_query("SELECT utime, qtime, ip_addr, code, size, url FROM detail WHERE u_id='$id' and url like('%".mysql_escape_string($site)."%')  ORDER BY $orderby limit ".($page*$pagestep).",$pagestep", $link);
    //$res = mysql_query("SELECT utime, qtime, ip_addr, code, size, url FROM service_detail WHERE u_id='$id' and site='$site'  ORDER BY $orderby limit ".($page*$pagestep).",$pagestep", $link);
    $res = mysql_query("SELECT utime, qtime, ip_addr, code, size, url FROM service_detail WHERE site='".mysql_real_escape_string($site)."'  ORDER BY $orderby limit ".($page*$pagestep).",$pagestep", $link);
//    if ( !$res )
//        return 3;
    for ($i = 0; $i < MYSQL_NUMROWS($res); $i++)
        {
            $utime  = mysql_result($res,$i,"utime");
            $size  = mysql_result($res,$i,"size");
            $qtime  = mysql_result($res,$i,"qtime");
            $ip_addr = long2ip(mysql_result($res,$i,"ip_addr"));
            $code = mysql_result($res,$i,"code");
            $url = mysql_result($res,$i,"url");

            echo "<TR>
<TD BGCOLOR=#FFF7E5>".strftime ("%B %d, %T", (int)$utime)."</TD>
<TD BGCOLOR=#FFF7E5 ALIGN=RIGHT>" . dotize($size) . "</TD>
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
    //$res = mysql_query("SELECT SUM(size) FROM service_detail WHERE u_id='$id' and url like('%".mysql_escape_string($site)."%')", $link);
    $res = mysql_query("SELECT SUM(size) FROM service_detail WHERE url like('%".mysql_real_escape_string($site)."%')", $link);
    $sum  = mysql_result($res,0);
    //$res = mysql_query("SELECT count(utime) FROM service_detail WHERE u_id='$id' and url like('%".mysql_escape_string($site)."%')", $link);
    $res = mysql_query("SELECT count(utime) FROM service_detail WHERE url like('%".mysql_real_escape_string($site)."%')", $link);
    $sites = mysql_result($res, 0); 
    mysql_close();
    echo "<BR>
<SPAN CLASS=\"smalltext\"><B>$word_total:</B> " . dotize($sum) . " $word_byte $web_client_downloaded_with ".dotize($sites)." $web_client_query.</SPAN>
";
    return true;
}

//
//      Функция show_service_stat($id) - обращается к таблице traf (база данных squid),
//                               выбирает оттуда информацию для заданного логина
//                               и представляет ее в HTML-формате
//      Вход : $user - login
//      Выход: если все удачно - true, иначе - код ошибки
//      Примечание 3: если программе не удается подключится к базе данных,
//                    возвращается код ошибки 2
//      Примечание 4: если программе не удается выполнить запрос к базе
//                    данных, возвращается код ошибки 3
//
function show_service_stat($link, $id)
{ 
global $o;
global $detailed;
global $mode;
global $lang;
if ($lang==0) {include "../inc/ru.php";};
if ($lang==1) {include "../inc/en.php";};

    switch ($o) {
        case "sizeD" : {
                        $orderby = "size DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "sizeA" : {
                        $orderby = "size ASC";
                        $o1 = "?o=sizeD";
                        $o2 = "?o=siteA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "siteD" : {
                        $orderby = "site DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteA";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php$o2\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        break;
                       }
        case "siteA" : {
                        $orderby = "site ASC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteD";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php$o2\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        break;
                       }
             default : {
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
    $res = mysql_query("SELECT * FROM service_site WHERE u_id=".(int)$id." ORDER BY $orderby", $link);
    if ( !$res )
        return 3;
    for ($i = 0; $i < MYSQL_NUMROWS($res); $i++)
        {
            $site  = mysql_result($res,$i,"site");
            $size  = mysql_result($res,$i,"size");
            $lutime = mysql_result($res,$i,"lutime");
            $futime = mysql_result($res,$i,"futime");


            echo "<TR>
<TD BGCOLOR=#FFF7E5 ALIGN=RIGHT>" . dotize($size) . "</TD>
<TD BGCOLOR=#FFF7E5>";
    if ($detailed==1)
      if ($mode=="admin") 
    {echo "<a href=\"index.php?id=$id&type=servicedetail&site=$site\">";}
      else 
    {echo "<a href=\"index.php?type=servicedetail&site=$site\">";};
echo "$site";
    if ($detailed==1) {echo "</a>";};
echo "<TD BGCOLOR=#FFF7E5>".strftime ("%B %d, %T", (int)$futime)."</TD>";
echo "<TD BGCOLOR=#FFF7E5>".strftime ("%B %d, %T", (int)$lutime)."</TD>";
echo "</TD></TR>";
        }
    echo "</TABLE></TD>
</TR>
</TABLE>
";
    $res = mysql_query("SELECT SUM(size) FROM service_site WHERE u_id=".(int)$id, $link);
    $sum  = mysql_result($res,0);
    $res = mysql_query("SELECT count(site) FROM service_site WHERE u_id=".(int)$id, $link);
    $sites = mysql_result($res, 0); 
    //mysql_close();
    echo "<BR><SPAN CLASS=\"smalltext\"><B>$word_total:</B> " . dotize($sum) . " $word_byte $web_client_downloaded_from $sites $web_client_sites_wrd.</SPAN>";
    return true;
}

//
//      Функция show_all_service_stat($id) - обращается к таблице traf (база данных squid),
//                               выбирает оттуда информацию для заданного логина
//                               и представляет ее в HTML-формате
//      Вход : $user - login
//      Выход: если все удачно - true, иначе - код ошибки
//      Примечание 3: если программе не удается подключится к базе данных,
//                    возвращается код ошибки 2
//      Примечание 4: если программе не удается выполнить запрос к базе
//                    данных, возвращается код ошибки 3
//
function show_all_service_stat($link, $id)
{ 
global $o;
global $detailed;
global $mode;
global $lang;
if ($lang==0) {include "../inc/ru.php";};
if ($lang==1) {include "../inc/en.php";};

echo "<br>Служебные сайты<br><br>";

    switch ($o) {
        case "sizeD" : {
                        $orderby = "size DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "sizeA" : {
                        $orderby = "size ASC";
                        $o1 = "?o=sizeD";
                        $o2 = "?o=siteA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "siteD" : {
                        $orderby = "site DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteA";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php$o2\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        break;
                       }
        case "siteA" : {
                        $orderby = "site ASC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteD";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php$o2\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        break;
                       }
             default : {
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
    #$res = mysql_query("SELECT * FROM service_site WHERE u_id=$id ORDER BY $orderby", $link);
    $res = mysql_query("SELECT * FROM service_site ORDER BY $orderby", $link);
    if ( !$res )
        return 3;
    for ($i = 0; $i < MYSQL_NUMROWS($res); $i++)
        {
            $site  = mysql_result($res,$i,"site");
            $size  = mysql_result($res,$i,"size");
            $lutime = mysql_result($res,$i,"lutime");
            $futime = mysql_result($res,$i,"futime");


            echo "<TR>
<TD BGCOLOR=#FFF7E5 ALIGN=RIGHT>" . dotize($size) . "</TD>
<TD BGCOLOR=#FFF7E5>";
    if ($detailed==1)
      if ($mode=="admin") 
    //{echo "<a href=\"index.php?id=$id&type=servicealldetail&site=$site\">";}
    {echo "<a href=\"index.php?type=servicealldetail&site=$site\">";}
      else 
    {echo "<a href=\"index.php?type=servicealldetail&site=$site\">";};
echo "$site";
    if ($detailed==1) {echo "</a>";};
echo "<TD BGCOLOR=#FFF7E5>".strftime ("%B %d, %T", (int)$futime)."</TD>";
echo "<TD BGCOLOR=#FFF7E5>".strftime ("%B %d, %T", (int)$lutime)."</TD>";
echo "</TD></TR>";
        }
    echo "</TABLE></TD>
</TR>
</TABLE>
";
    $res = mysql_query("SELECT SUM(size) FROM service_site", $link);
    $sum  = mysql_result($res,0);
    $res = mysql_query("SELECT distinct count(site) FROM service_site", $link);
    $sites = mysql_result($res, 0); 
    //mysql_close();
    echo "<BR><SPAN CLASS=\"smalltext\"><B>$word_total:</B> " . dotize($sum) . " $word_byte $web_client_downloaded_from $sites $web_client_sites_wrd.</SPAN>";
    return true;
}

//      Функция show_all_stat($id) - обращается к таблице traf (база данных squid),
//                                   выбирает оттуда всю информацию о посещенных сайтах
//                                   и представляет ее в HTML-формате
//      Вход : $user - login
//      Выход: если все удачно - true, иначе - код ошибки
//      Примечание 3: если программе не удается подключится к базе данных,
//                    возвращается код ошибки 2
//      Примечание 4: если программе не удается выполнить запрос к базе
//                    данных, возвращается код ошибки 3
//
function show_all_stat($link, $id)
{ 
global $o;
global $detailed;
global $mode;
global $lang;
global $settings;
if ($lang==0) {include "../inc/ru.php";};
if ($lang==1) {include "../inc/en.php";};

$page=(int)$_GET['page'];
// ----------------------------
$pagestep=$settings['pagelen'];
//-----------------------------
$cquery=$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'];
if (0<strpos($cquery,"&page=")) {$cquery=substr($cquery,0,strpos($cquery,"&page="));};
echo "<br><center><p style=\"font-size: 12\">";
$result = mysql_query("SELECT count(site) as rec FROM site", $link);
$pages= floor(mysql_result( $result, 0, "rec")/$pagestep);

if ($page!=0) echo "<a href=\"$cquery"."&page=0\"><<</a> ";
if ($page>10) echo "<a href=\"$cquery&page=".($page-10)."\"><</a> ";
if ($page-5>0) 
{
	for ($i = $page-5; $i < $page; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
else 
{
	for ($i = 1; $i < $page; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
echo "<b>$page</b> ";
if ($page+5<$pages) 
{
	for ($i = $page+1; $i < $page+6; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
else
{
	for ($i = $page+1; $i < $pages; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}

if (($page+10)<$pages) {echo "<a href=\"$cquery&page=".($page+10)."\">></a> ";}
if ($page<$pages) {echo "<a href=\"$cquery&page=".$pages."\">>></a> ";}

echo "</p>";

    switch ($o) {
        case "sizeD" : {
                        $orderby = "size DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "sizeA" : {
                        $orderby = "size ASC";
                        $o1 = "?o=sizeD";
                        $o2 = "?o=siteA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "siteD" : {
                        $orderby = "site DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteA";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php$o2\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        break;
                       }
        case "siteA" : {
                        $orderby = "site ASC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteD";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php$o2\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        break;
                       }
             default : {
                        $orderby = "size DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        $p2 = "";
                       }
    }

echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0>";
echo "<TR>";
echo "<TD BGCOLOR=#93BEE2>";
echo "<TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1>";
echo "<TR>";
echo "<TD ALIGN=CENTER BGCOLOR=$b1>$p1<B><A HREF=\"index.php$o1&type=allsites\" STYLE=\"color:#000000; text-decoration: underline\">$web_client_downloaded</A></B></TD>";
echo "<TD ALIGN=CENTER BGCOLOR=$b2>$p2<B><A HREF=\"index.php$o2&type=allsites\" STYLE=\"color:#000000; text-decoration: underline\">$web_client_site</A></B></TD>";
echo "<TD ALIGN=CENTER BGCOLOR=#bfbfbf><B>$web_client_futime</B></TD>";
echo "<TD ALIGN=CENTER BGCOLOR=#bfbfbf><B>$web_client_lutime</B></TD>";
echo "</TR>";

    //$res = mysql_query("SELECT * FROM site WHERE u_id=$id ORDER BY $orderby", $link);
    //$res = mysql_query("SELECT u_id, utime, qtime, ip_addr, code, size, url FROM detail WHERE url like('%".mysql_escape_string($site)."%')  ORDER BY $orderby limit ".($page*$pagestep).",$pagestep", $link);
    $res = mysql_query("SELECT * FROM site ORDER BY $orderby LIMIT ".($page*$pagestep).",$pagestep", $link);
    if ( !$res )
        return 3;
    for ($i = 0; $i < MYSQL_NUMROWS($res); $i++)
        {
            $site  = mysql_result($res,$i,"site");
            $size  = mysql_result($res,$i,"size");
            $lutime = mysql_result($res,$i,"lutime");
            $futime = mysql_result($res,$i,"futime");


            echo "<TR>
<TD BGCOLOR=#FFF7E5 ALIGN=RIGHT>" . dotize($size) . "</TD>
<TD BGCOLOR=#FFF7E5>";
    if ($detailed==1)
      if ($mode=="admin") 
    //{echo "<a href=\"index.php?id=$id&type=detail&site=$site\">";}
    {echo "<a href=\"index.php?type=alldetail&site=$site\">";}
      else 
    //{echo "<a href=\"index.php?type=detail&site=$site\">";};
    {echo "<a href=\"index.php?type=alldetail&site=$site\">";};
echo "$site";
    if ($detailed==1) {echo "</a>";};
echo "<TD BGCOLOR=#FFF7E5>".strftime ("%B %d, %T", (int)$futime)."</TD>";
echo "<TD BGCOLOR=#FFF7E5>".strftime ("%B %d, %T", (int)$lutime)."</TD>";
echo "</TD></TR>";
        }
    echo "</TABLE></TD>
</TR>
</TABLE>
";
    #$res = mysql_query("SELECT SUM(size) FROM site WHERE u_id=$id", $link);
    $res = mysql_query("SELECT SUM(size) FROM site", $link);
    $sum  = mysql_result($res,0);
    #$res = mysql_query("SELECT count(site) FROM site WHERE u_id=$id", $link);
    $res = mysql_query("SELECT count(site) FROM site", $link);
    $sites = mysql_result($res, 0); 
    mysql_close();
    echo "<BR><SPAN CLASS=\"smalltext\"><B>$word_total:</B> " . dotize($sum) . " $word_byte $web_client_downloaded_from $sites $web_client_sites_wrd.</SPAN>";
    return true;
}



//
//      Функция show_all_detail() - обращается к таблице detail (база данных trf),
//                               выбирает оттуда информацию о всех сайтах
//                               и представляет ее в HTML-формате
//      Вход : 
//      Выход: если все удачно - true, иначе - код ошибки
//      Примечание 3: если программе не удается подключится к базе данных,
//                    возвращается код ошибки 2
//      Примечание 4: если программе не удается выполнить запрос к базе
//                    данных, возвращается код ошибки 3
//
function show_all_detail($link, $id, $site)
{ 
global $o;
global $mode;
global $lang;
global $settings;

$page=(int)$_GET['page'];
// ----------------------------
$pagestep=$settings['pagelen'];
//-----------------------------
if (""==$site) {echo "строка сайта не может быть пустой!";return 1;};
$cquery=$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'];
if (0<strpos($cquery,"&page=")) {$cquery=substr($cquery,0,strpos($cquery,"&page="));};
echo "<br><center><p style=\"font-size: 12\">";
$result = mysql_query("SELECT count(utime) as rec FROM detail WHERE u_id='".(int)$id."' and url like('%".mysql_real_escape_string($site)."%')", $link);
$pages= floor(mysql_result( $result, 0, "rec")/$pagestep);

if ($page!=0) echo "<a href=\"$cquery"."&page=0\"><<</a> ";
if ($page>10) echo "<a href=\"$cquery&page=".($page-10)."\"><</a> ";
if ($page-5>0) 
{
	for ($i = $page-5; $i < $page; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
else 
{
	for ($i = 1; $i < $page; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
echo "<b>$page</b> ";
if ($page+5<$pages) 
{
	for ($i = $page+1; $i < $page+6; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
else
{
	for ($i = $page+1; $i < $pages; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}

if (($page+10)<$pages) {echo "<a href=\"$cquery&page=".($page+10)."\">></a> ";}
if ($page<$pages) {echo "<a href=\"$cquery&page=".$pages."\">>></a> ";}

echo "</p>";




  if ($mode=="admin") 
    {$query="&type=detail&id=".$_GET['id']."&site=$site";}
  else
    {$query="&type=detail&site=$site";};
    switch ($o) {
        case "sizeD" : {
                        $orderby = "size DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=timeA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php".$o1.$query."\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "sizeA" : {
                        $orderby = "size ASC";
                        $o1 = "?o=sizeD";
                        $o2 = "?o=timeA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php".$o1.$query."\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "timeD" : {
                        $orderby = "utime DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=timeA";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php".$o2.$query."\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        break;
                       }
        case "timeA" : {
                        $orderby = "utime ASC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=timeD";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php".$o2.$query."\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        break;
                       }
             default : {
                        $orderby = "size DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=timeA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php".$o1.$query."\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        $p2 = "";
                       }
    }



if ($_COOKIE['lang']==0) {include "../inc/ru.php";};
if ($_COOKIE['lang']==1) {include "../inc/en.php";};

echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR>
<TD BGCOLOR=#93BEE2><TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1><TR>
<TD ALIGN=CENTER BGCOLOR=$b2>$p2<B><A HREF=\"index.php$o2$query\" STYLE=\"color:#000000; text-decoration: underline\">$word_time</A></B></TD>
<TD ALIGN=CENTER BGCOLOR=$b1>$p1<B><A HREF=\"index.php$o1$query\" STYLE=\"color:#000000; text-decoration: underline\">$word_byte</A></B></TD>
<TD ALIGN=LEFT BGCOLOR=#FFFFFF><b>$web_client_query</B></TD>
<TD ALIGN=RIGHT BGCOLOR=#FFFFFF><b>msec</B></TD>
<TD ALIGN=LEFT BGCOLOR=#FFFFFF><b>$web_client_src_ip</B></TD>
<TD ALIGN=LEFT BGCOLOR=#FFFFFF><b>$web_client_server_rply</B></TD></TR>";

    $res = mysql_query("SELECT u_id, utime, qtime, ip_addr, code, size, url FROM detail WHERE url like('%".mysql_real_escape_string($site)."%')  ORDER BY $orderby limit ".($page*$pagestep).",$pagestep", $link);
//    if ( !$res )
//        return 3;
    for ($i = 0; $i < MYSQL_NUMROWS($res); $i++)
        {
            $uid  = mysql_result($res,$i,"u_id");
            $utime  = mysql_result($res,$i,"utime");
            $size  = mysql_result($res,$i,"size");
            $qtime  = mysql_result($res,$i,"qtime");
            $ip_addr = long2ip(mysql_result($res,$i,"ip_addr"));
            $code = mysql_result($res,$i,"code");
            $url = mysql_result($res,$i,"url");

            echo "<TR>
<TD BGCOLOR=#FFF7E5>".strftime ("%B %d, %T", (int)$utime)."</TD>
<TD BGCOLOR=#FFF7E5 ALIGN=RIGHT>" . dotize($size) . "</TD>
<TD BGCOLOR=#FFF7E5 ALIGN=LEFT><a href=\"index.php?type=sites&id=$uid&site=$site\">$url</a></TD>
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
    $res = mysql_query("SELECT SUM(size) FROM detail WHERE url like('%".mysql_real_escape_string($site)."%')", $link);
    $sum  = mysql_result($res,0);
    $res = mysql_query("SELECT count(utime) FROM detail WHERE url like('%".mysql_real_escape_string($site)."%')", $link);
    $sites = mysql_result($res, 0); 
    mysql_close();
    echo "<BR>
<SPAN CLASS=\"smalltext\"><B>$word_total:</B> " . dotize($sum) . " $word_byte $web_client_downloaded_with ".dotize($sites)." $web_client_query.</SPAN>
";
    return true;

}

//
//      Функция show_service_info($id) - для заданного id'a делает выборку в
//                               таблице (база данных) и представляет 
//                               полученные данные в HTML-формате
//      Вход : $id - username
//      Выход: если все удачно - true, иначе - код ошибки
//      Примечание: если программе не удается выполнить запрос к базе
//                    данных, возвращается код ошибки 3
//
function show_service_info($link, $id)
{
global $megabyte_cost;
global $lang;
if ($lang==0) {include "../inc/ru.php";};
if ($lang==1) {include "../inc/en.php";};

    $res = mysql_query("SELECT u.login as login, u.quota as quota, u.used as used, u.email as email, u.descr as descr, a.vname as aid FROM users u, acl a WHERE u.id=".(int)$id." and u.aid=a.id", $link);
    if ( !$res ) {
    	echo mysql_error();
        return 3;}
    $i=0;
    if ( 0==mysql_numrows($res)) {echo "error: $web_client_nouser";
    return false;
    }

    $nick  = mysql_result($res,$i,"login");
    $lim = mysql_result($res,$i,"quota");
    $cur = mysql_result($res,$i,"used");
    $email = mysql_result($res,$i,"email");
    $descr = mysql_result($res,$i,"descr");
    $timeacl = mysql_result($res,$i,"aid");
    $rcpt = "rcpt='" . str_replace(",", "' OR rcpt='", $email) . "'";
    $res =  mysql_query("SELECT SUM(size) FROM mail WHERE $rcpt", $link);
     $msum  = @mysql_result($res,0);
    $msum = dotize($msum);
$cur = $cur -1;
if ($megabyte_cost>0) {
echo "$web_client_mbcost $megabyte_cost";
};
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
if ($megabyte_cost>0) {
echo "<TR><TD><B>$web_client_account_total</B></TD><TD ALIGN=RIGHT>&nbsp;"; 
if ($lim != "0") {echo " " . dotize((int)(($megabyte_cost*(int)($lim-$cur))/(1024*1024))) . " "; }
else {echo "$web_client_unlim";}
echo "</TD>
<TD>&nbsp;руб.</TD>
</TR>";
};
echo"<TR><TD><B>Квота:</B></TD>
<TD ALIGN=RIGHT>&nbsp;"; 
if ($lim != "0") {echo " " . dotize($lim) . " "; }
else {echo "$web_client_unlim";}
echo "</TD>
<TD>&nbsp;$word_byte</TD>
</TR>
<TR>
<TD><B>$web_client_used</B></TD>
<TD ALIGN=RIGHT>&nbsp;" . dotize($cur) . "</TD>";
if ((int)($msum)>0) {
        echo "<TD>&nbsp;$word_byte [$web_client_in_mail_trf $msum" .  " $word_byte ]</TD>";
};
echo "</TR><TR>
<TD><B>Осталось (байт):</B></TD>
<TD ALIGN=RIGHT>&nbsp;"; 
if ($lim != "0") {echo " " . dotize((int)($lim - $cur)) . " "; }
else {echo "$web_client_unlim";}
echo "</TD>
<TD>&nbsp;$word_byte</TD>
</TR>
<TR>
<TD><B>$work_time</B></TD>
<TD COLSPAN=2>&nbsp;$timeacl<TD>&nbsp;</TD>
</TR>
</TABLE>
";
@mysql_free_result($res);
return true;

}

//
//      Функция show_stat($id) - обращается к таблице traf (база данных squid),
//                               выбирает оттуда информацию для заданного логина
//                               и представляет ее в HTML-формате
//      Вход : $user - login
//      Выход: если все удачно - true, иначе - код ошибки
//      Примечание 3: если программе не удается подключится к базе данных,
//                    возвращается код ошибки 2
//      Примечание 4: если программе не удается выполнить запрос к базе
//                    данных, возвращается код ошибки 3
//
function show_history_stat($link, $id)
{
global $o;
global $detailed;
global $mode;
global $lang;
if ($lang==0) {include "../inc/ru.php";};
if ($lang==1) {include "../inc/en.php";};

    switch ($o) {
        case "sizeD" : {
                        $orderby = "size DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "sizeA" : {
                        $orderby = "size ASC";
                        $o1 = "?o=sizeD";
                        $o2 = "?o=siteA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "siteD" : {
                        $orderby = "site DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteA";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php$o2\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        break;
                       }
        case "siteA" : {
                        $orderby = "site ASC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteD";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php$o2\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        break;
                       }
             default : {
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
<TD ALIGN=CENTER BGCOLOR=$b1>$p1<B><A HREF=\"index.php$o1&type=shist&id=$id\" STYLE=\"color:#000000; text-decoration: underline\">$web_client_downloaded</A></B></TD>
<TD ALIGN=CENTER BGCOLOR=$b2>$p2<B><A HREF=\"index.php$o2&type=shist&id=$id\" STYLE=\"color:#000000; text-decoration: underline\">$web_client_site</A></B></TD>
<!--<TD ALIGN=CENTER BGCOLOR=#bfbfbf><B>$web_client_futime</B></TD>
<TD ALIGN=CENTER BGCOLOR=#bfbfbf><B>$web_client_lutime</B></TD>-->
</TR>
";

    #$result = mysql_query("SELECT * FROM uhistory where utime=$utime $ORDER_BY", $link);
    #$res = mysql_query("SELECT * FROM site WHERE u_id=$id ORDER BY $orderby", $link);
    $res = mysql_query("SELECT * FROM shistory WHERE uh_id=".(int)$id." ORDER BY $orderby", $link);
    if ( !$res )
        return 3;
    for ($i = 0; $i < MYSQL_NUMROWS($res); $i++)
        {
            $site  = mysql_result($res,$i,"site");
            $size  = mysql_result($res,$i,"size");
            #$lutime = mysql_result($res,$i,"lutime");
            #$futime = mysql_result($res,$i,"futime");


            echo "<TR>
<TD BGCOLOR=#FFF7E5 ALIGN=RIGHT>" . dotize($size) . "</TD>
<TD BGCOLOR=#FFF7E5>";
    //if ($detailed==1)
    //  if ($mode=="admin")
    //{echo "<a href=\"index.php?id=$id&type=detail&site=$site\">";}
    //  else
    //{echo "<a href=\"index.php?type=detail&site=$site\">";};
echo "$site";
    if ($detailed==1) {echo "</a>";};
#echo "<TD BGCOLOR=#FFF7E5>".strftime ("%B %d, %T", (int)$futime)."</TD>";
#echo "<TD BGCOLOR=#FFF7E5>".strftime ("%B %d, %T", (int)$lutime)."</TD>";
echo "</TD></TR>";
        }
    echo "</TABLE></TD>
</TR>
</TABLE>
";
    #$res = mysql_query("SELECT SUM(size) FROM site WHERE u_id=$id", $link);
    $res = mysql_query("SELECT SUM(size) FROM shistory WHERE uh_id=".(int)$id, $link);
    $sum  = mysql_result($res,0);
    #$res = mysql_query("SELECT count(site) FROM site WHERE u_id=$id", $link);
    $res = mysql_query("SELECT count(site) FROM shistory WHERE uh_id=".(int)$id, $link);
    $sites = mysql_result($res, 0);
    mysql_close();
    echo "<BR><SPAN CLASS=\"smalltext\"><B>$word_total:</B> " . dotize($sum) . " $word_byte $web_client_downloaded_from $sites $web_client_sites_wrd.</SPAN>";
    return true;
}

//
//      Функция show_history_info($id) - для заданного id'a делает выборку в
//                               таблице (база данных) и представляет
//                               полученные данные в HTML-формате
//      Вход : $id - username
//      Выход: если все удачно - true, иначе - код ошибки
//      Примечание: если программе не удается выполнить запрос к базе
//                    данных, возвращается код ошибки 3
//
function show_history_info($link, $id)
{
global $megabyte_cost;
global $lang;
if ($lang==0) {include "../inc/ru.php";};
if ($lang==1) {include "../inc/en.php";};

    #$res = mysql_query("SELECT u.login as login, u.quota as quota, u.used as used, u.email as email, u.descr as descr, a.vname as aid FROM users u, acl a WHER
    $res = mysql_query("SELECT u.login as login, u.quota as quota, u.used as used, u.descr as descr FROM uhistory u WHERE u.id=".(int)$id."", $link);
    if ( !$res ) {
        echo mysql_error();
        return 3;}
    $i=0;
    if ( 0==mysql_numrows($res)) {echo "error: $web_client_nouser";
    return false;
    }

    $nick  = mysql_result($res,$i,"login");
    $lim = mysql_result($res,$i,"quota");
    $cur = mysql_result($res,$i,"used");
    #$email = mysql_result($res,$i,"email");
    $descr = mysql_result($res,$i,"descr");
    #$timeacl = mysql_result($res,$i,"aid");
    $rcpt = "rcpt='" . str_replace(",", "' OR rcpt='", $email) . "'";
    $res =  mysql_query("SELECT SUM(size) FROM mail WHERE $rcpt", $link);
     $msum  = @mysql_result($res,0);
    $msum = dotize($msum);
$cur = $cur -1;
if ($megabyte_cost>0) {
echo "$web_client_mbcost $megabyte_cost";
};
echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0>
<TR>
<TD><B>$view_user</B></TD>
<TD>&nbsp;$descr</TD>
</TR>
<TR>
<TD><B>$view_login</B></TD>
<TD>&nbsp;$nick</TD>
</TR>
<!--<TR><TD><B>$view_email</B></TD>
<TD>&nbsp;$email</TD></TR>-->";
echo "</TABLE><BR><TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0>";
if ($megabyte_cost>0) {
echo "<TR><TD><B>$web_client_account_total</B></TD><TD ALIGN=RIGHT>&nbsp;";
if ($lim != "0") {echo " " . dotize((int)(($megabyte_cost*(int)($lim-$cur))/(1024*1024))) . " "; }
else {echo "$web_client_unlim";}
echo "</TD>
<TD>&nbsp;руб.</TD>
</TR>";
};
echo"<TR><TD><B>Квота:</B></TD>
<TD ALIGN=RIGHT>&nbsp;";
if ($lim != "0") {echo " " . dotize($lim) . " "; }
else {echo "$web_client_unlim";}
echo "</TD>
<TD>&nbsp;$word_byte</TD>
</TR>
<TR>
<TD><B>$web_client_used</B></TD>
<TD ALIGN=RIGHT>&nbsp;" . dotize($cur) . "</TD>";
if ((int)($msum)>0) {
        echo "<TD>&nbsp;$word_byte [$web_client_in_mail_trf $msum" .  " $word_byte ]</TD>";
};
echo "</TR><TR>
<TD><B>Осталось (байт):</B></TD>
<TD ALIGN=RIGHT>&nbsp;";
if ($lim != "0") {echo " " . dotize((int)($lim - $cur)) . " "; }
else {echo "$web_client_unlim";}
echo "</TD>
<TD>&nbsp;$word_byte</TD>
</TR>
<TR>
<!--<TD><B>$work_time</B></TD>
<TD COLSPAN=2>&nbsp;$timeacl<TD>&nbsp;</TD>-->
</TR>
</TABLE>
";
@mysql_free_result($res);
return true;

}

//
//      Функция show_all_stat($id) - обращается к таблице traf (база данных squid),
//                                   выбирает оттуда всю информацию о посещенных сайтах
//                                   и представляет ее в HTML-формате
//      Вход : $user - login
//      Выход: если все удачно - true, иначе - код ошибки
//      Примечание 3: если программе не удается подключится к базе данных,
//                    возвращается код ошибки 2
//      Примечание 4: если программе не удается выполнить запрос к базе
//                    данных, возвращается код ошибки 3
//
//function show_all_history_stat($link, $id)
function show_all_history_stat($link)
{ 
global $o;
global $detailed;
global $mode;
global $lang;
global $settings;
global $utime;
if ($lang==0) {include "../inc/ru.php";};
if ($lang==1) {include "../inc/en.php";};

$page=(int)$_GET['page'];
// ----------------------------
$pagestep=$settings['pagelen'];
//-----------------------------
$cquery=$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'];
if (0<strpos($cquery,"&page=")) {$cquery=substr($cquery,0,strpos($cquery,"&page="));};
echo "<br><center><p style=\"font-size: 12\">";
$result = mysql_query("SELECT count(site) as rec FROM site", $link);
$pages= floor(mysql_result( $result, 0, "rec")/$pagestep);

if ($page!=0) echo "<a href=\"$cquery"."&page=0\"><<</a> ";
if ($page>10) echo "<a href=\"$cquery&page=".($page-10)."\"><</a> ";
if ($page-5>0) 
{
	for ($i = $page-5; $i < $page; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
else 
{
	for ($i = 1; $i < $page; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
echo "<b>$page</b> ";
if ($page+5<$pages) 
{
	for ($i = $page+1; $i < $page+6; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}
else
{
	for ($i = $page+1; $i < $pages; $i++) {echo "<a href=\"$cquery&page=$i\">$i</a> ";}
}

if (($page+10)<$pages) {echo "<a href=\"$cquery&page=".($page+10)."\">></a> ";}
if ($page<$pages) {echo "<a href=\"$cquery&page=".$pages."\">>></a> ";}

echo "</p>";

    switch ($o) {
        case "sizeD" : {
                        $orderby = "size DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "sizeA" : {
                        $orderby = "size ASC";
                        $o1 = "?o=sizeD";
                        $o2 = "?o=siteA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        $p2 = "";
                        break;
                       }
        case "siteD" : {
                        $orderby = "site DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteA";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php$o2\"><IMG ALT=\"$web_client_order_desc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/desc.gif></A>&nbsp;";
                        break;
                       }
        case "siteA" : {
                        $orderby = "site ASC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteD";
                        $b1 = "#FFFFFF";
                        $b2 = "#93BEE2";
                        $p1 = "";
                        $p2 = "<A HREF=\"index.php$o2\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        break;
                       }
             default : {
                        $orderby = "size DESC";
                        $o1 = "?o=sizeA";
                        $o2 = "?o=siteA";
                        $b1 = "#93BEE2";
                        $b2 = "#FFFFFF";
                        $p1 = "<A HREF=\"index.php$o1\"><IMG ALT=\"$web_client_order_asc\" BORDER=0 HEIGHT=7 WIDTH=7 SRC=../images/asc.gif></A>&nbsp;";
                        $p2 = "";
                       }
    }

echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0>";
echo "<TR>";
echo "<TD BGCOLOR=#93BEE2>";
echo "<TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1>";
echo "<TR>";
echo "<TD ALIGN=CENTER BGCOLOR=$b1>$p1<B><A HREF=\"index.php$o1&type=allshist&utime=$utime\" STYLE=\"color:#000000; text-decoration: underline\">$web_client_downloaded</A></B></TD>";
echo "<TD ALIGN=CENTER BGCOLOR=$b2>$p2<B><A HREF=\"index.php$o2&type=allshist&utime=$utime\" STYLE=\"color:#000000; text-decoration: underline\">$web_client_site</A></B></TD>";
#echo "<TD ALIGN=CENTER BGCOLOR=#bfbfbf><B>$web_client_futime</B></TD>";
#echo "<TD ALIGN=CENTER BGCOLOR=#bfbfbf><B>$web_client_lutime</B></TD>";
echo "</TR>";

    #$res = mysql_query("SELECT * FROM site WHERE u_id=$id ORDER BY $orderby", $link);
    #$res = mysql_query("SELECT u_id, utime, qtime, ip_addr, code, size, url FROM detail WHERE url like('%".mysql_escape_string($site)."%')  ORDER BY $orderby limit ".($page*$pagestep).",$pagestep", $link);
    #$res = mysql_query("SELECT * FROM site ORDER BY $orderby LIMIT ".($page*$pagestep).",$pagestep", $link);
    $res = mysql_query("SELECT s.site as site, s.size as size FROM uhistory u, shistory s WHERE u.utime=$utime and u.id=s.uh_id ORDER BY $orderby LIMIT ".($page*$pagestep).",$pagestep", $link);
    #$res = mysql_query("SELECT s.site as site, s.size as size FROM uhistory u, shistory s WHERE u.utime=$utime and u.id=s.uh_id LIMIT ".($page*$pagestep).",$pagestep", $link);
    #$res = mysql_query("SELECT s.site as site, s.size as size FROM uhistory u, shistory s WHERE u.utime=$utime and u.id=s.uh_id", $link);
    #$res = mysql_query("SELECT * FROM shistory ORDER BY $orderby LIMIT ".($page*$pagestep).",$pagestep", $link);
    if ( !$res )
        return 3;
    for ($i = 0; $i < MYSQL_NUMROWS($res); $i++)
        {
            $site  = mysql_result($res,$i,"site");
            $size  = mysql_result($res,$i,"size");
            #$lutime = mysql_result($res,$i,"lutime");
            #$futime = mysql_result($res,$i,"futime");


            echo "<TR>
<TD BGCOLOR=#FFF7E5 ALIGN=RIGHT>" . dotize($size) . "</TD>
<TD BGCOLOR=#FFF7E5>";
    if ($detailed==1)
      if ($mode=="admin") 
    //{echo "<a href=\"index.php?id=$id&type=detail&site=$site\">";}
    //{echo "<a href=\"index.php?type=alldetail&site=$site\">";}
    //  else 
    //{echo "<a href=\"index.php?type=detail&site=$site\">";};
    //{echo "<a href=\"index.php?type=alldetail&site=$site\">";};
echo "$site";
    if ($detailed==1) {echo "</a>";};
#echo "<TD BGCOLOR=#FFF7E5>".strftime ("%B %d, %T", (int)$futime)."</TD>";
#echo "<TD BGCOLOR=#FFF7E5>".strftime ("%B %d, %T", (int)$lutime)."</TD>";
echo "</TD></TR>";
        }
    echo "</TABLE></TD>
</TR>
</TABLE>
";
    #$res = mysql_query("SELECT SUM(size) FROM site WHERE u_id=$id", $link);
    #$res = mysql_query("SELECT SUM(size) FROM site", $link);
    #$res = mysql_query("SELECT SUM(size) FROM shistory", $link);
    $res = mysql_query("SELECT SUM(s.size) as size FROM uhistory u, shistory s WHERE u.utime=$utime and u.id=s.uh_id", $link);
    $sum  = mysql_result($res,0);
    #$res = mysql_query("SELECT count(site) FROM site WHERE u_id=$id", $link);
    #$res = mysql_query("SELECT count(site) FROM site", $link);
    #$res = mysql_query("SELECT count(site) FROM shistory", $link);
    $res = mysql_query("SELECT SUM(s.site) as site FROM uhistory u, shistory s WHERE u.utime=$utime and u.id=s.uh_id", $link);
    $sites = mysql_result($res, 0); 
    mysql_close();
    echo "<BR><SPAN CLASS=\"smalltext\"><B>$word_total:</B> " . dotize($sum) . " $word_byte $web_client_downloaded_from $sites $web_client_sites_wrd.</SPAN>";
    return true;
}

?>
