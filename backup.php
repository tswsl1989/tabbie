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
if ($database_password)
    $password = "-p$database_password";
else
    $password = "";
$command = "mysqldump -u$database_user $password $database_name";
$command2 = "mysqldump -u$database_user -p******** $database_name";

$output = array();
$return_value = "undefined";

exec($command, &$output, &$return_value);

if ($return_value == 0) {
    header('Content-type: text/x-sql'); 
    header('Content-Disposition: attachment; filename="' . $database_name . '.sql"');
    foreach ($output as $line)
        print "$line\n";
} else {

$ntu_controller = "backup";
$title = "Tabbie - Backup Failed";

require("view/header.php");
require("view/mainmenu.php");

?> <h3>Backup Failed</h3> <?

if ($_SERVER["SERVER_NAME"] == "tabbie.sourceforge.net") {
?>
The Backup module does not work in the online demo because mysqldump is not installed at
sourceforge. If you want have the backup functionality integrated into your local system, make sure to install on a Linux system, with enough rights to install mysqldump.
<? } else { ?>
There was a problem executing the command '<?= $command2 ?>', error code: <?= $return_value ?><br>

Make sure:
<ul>
<li>You are running this script in a Linux/Unix environment. As of yet, the included backup functionality only works on these Operating Systems.</li>
<li>You have installed mysqldump and it is available from the path</li>
<li>You have sufficient rights on this server.</li>
</ul>
Windows users can use one of the following systems:
<ul>
<li>EasyPHP</li>
<li>phpMyBackupPro</li>
</ul>
<? } ?>
<?php
require('view/footer.php'); 
}
?>
