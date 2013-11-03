<?php /* begin license *
 * 
 *     Tabbie, Debating Tabbing Software
 *     Copyright Contributors
 * 
 *     This file is part of Tabbie
 * 
 *     Tabbie is free software; you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation; either version 2 of the License, or
 *     (at your option) any later version.
 * 
 *     Tabbie is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 * 
 *     You should have received a copy of the GNU General Public License
 *     along with Tabbie; if not, write to the Free Software
 *     Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * end license */

require_once("config/settings.php");
require_once("adodb/adodb.inc.php");

mysql_connect($database_host, $database_user, $database_password);
mysql_select_db($database_name);
mysql_query("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");
global $DBconn;
$DBConn = &ADONewConnection("mysqli://$database_user:$database_password@$database_host/$database_name");
$DBConn->SetFetchMode(ADODB_FETCH_ASSOC);
?>
