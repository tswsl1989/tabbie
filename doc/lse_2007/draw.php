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

include("./lse_dev/config/dbconnection.php"); //Database Connection

$moduletype=trim($_GET['moduletype']); //module type : round currentdraw
if (!$moduletype) $moduletype="round"; //set to round if empty


//Check Database
$query="SHOW TABLES LIKE 'draw_round%'";
$result=mysql_query($query);
$numdraws=mysql_num_rows($result);

//Calculate Next Round
$nextround=$numdraws+1;


switch($moduletype)
{
    case "round":
    case "currentdraw":
    case "manualdraw":
                        break;

    default:
                        $moduletype="round"; 
}

//Load respective module
include("./lse_dev/draw/$moduletype.php");
?>          
