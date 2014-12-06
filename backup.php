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

$ntu_controller = "backup";
$moduletype="";
$title = "Backup";

require("view/header.php");
require("view/mainmenu.php");

?>

<h2>Database backup options</h2>
<ul>
    <li><a href="backup/mysqldump.php">Dump database using mysqldump</a><br />This is the older method, and should work fine. If in doubt, use this option</li>
    <li><a href="backup/adodbdump.php">Dump database using ADODB code</a><br />This method is newer and less tested. Does not depend on mysqldump being available</li>
    <li><a href="backup/dumpuniversity.php">Dump university list for use during future installs</a><br />
Download this file and put it in the <span style="font-family: monospace;">install/</span> folder if you want to re-use the current university list during future installations of Tabbie</li>
</ul>

<?php
require('view/footer.php'); 
?>
