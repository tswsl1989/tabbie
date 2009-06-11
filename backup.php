<?php /* begin license *
 * 
 *     Tabbie, Debating Tabbing Software
 *     Copyright Contributors
 *	   Portions of this code copyright Huang Kai 2006 GPL v2 only where marked.
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


exec($command, $output, $return_value);


if ($return_value == 0) {
    header('Content-type: text/plain'); 
    header('Content-Disposition: attachment; filename="' . $database_name . '.sql"');
    header( "Content-Description: File Transfer");
    foreach ($output as $line)
        print "$line\n";

} else { // attempt 2 (assuming windows/ WOS):

$strip_diskname = 2; // "C:"
$script_directory = substr($_SERVER['SCRIPT_FILENAME'], $strip_diskname, strrpos($_SERVER['SCRIPT_FILENAME'], '/') - $strip_diskname);
$command = "cd \"\\$script_directory/../../mysql/bin/\" && $command";

$output = array();
$return_value = "undefined";

exec($command, $output, $return_value);

if ($return_value == 0) {
    header('Content-type: text/plain'); 
    header('Content-Disposition: attachment; filename="' . $database_name . '.sql"');
    header( "Content-Description: File Transfer");

    foreach ($output as $line)
        print "$line\n";

} else { //Give up

$ntu_controller = "backup";
$title = "Tabbie - Backup Failed";

require("view/header.php");
require("view/mainmenu.php");

?> <h3>Backup Failed</h3> 
<p>
There was a problem executing the command '<?= $command ?>', error code: <?= $return_value ?><br>
<?
    foreach ($output as $line)
        print "$line <br>";
?>
</p>

<h4>Windows, (all in one installation / Webserver on a Stick)</h4>
<p>
This is an unknown problem. Please contact Klaas and report this problem as the backup utility is an important feature that we would like to have working.
</p>

<h4>Linux</h4>
<p>
Make sure you have installed mysqldump and it is available from the path. 
</p>

<h4>Your own server</h4>
<p>
If you're running Tabbie on something you created yourself, either contact Klaas for help or just take a look in this file and adapt paths to your liking. You'll be needing the executable "mysqldump" somewhere and then point relevant paths to it to have this work.
</p>

<?php
require('view/footer.php'); 
}}
?>
