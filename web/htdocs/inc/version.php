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

// $Author: slavik $ $Rev: 113 $
// $Id: version.php 113 2012-11-20 11:42:29Z slavik $

ob_start();
//--------------------------------------------------------- 
//   Данная хреновина подключает основной конфиг лежащий в 
// каталоге sacc/etc для этого нужно указывать полный путь.
// (C) the.Virus icq#210990
// Тута всяка хрень понаписана, можете почитать, 
// впринципе, если канать не будет - будет орать...
// --------------------------------------------------------
// SAcc Web interface and authorization config-injector.
// Тут находится указание ПОЛНОГО ПУТИ к файлу веб конфига.
require_once "/usr/local/sacc/etc/pref.php";

?>
