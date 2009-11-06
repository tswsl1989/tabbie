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


/* WARNING:
   INCLUDE OTHER TABBIE FILES WITH CARE. MANY OF THEM WILL FAIL WITHOUT config/settings.php
*/

$title = "Install Tabbie";
$ntu_controller = $moduletype = "";
require_once("view/header.php");
require_once("includes/dbimport.php");
?>
    <h2>Installation</h2>
<?php
$action="";
if(array_key_exists("action", @$_POST)) $action=trim(@$_POST['action']);
$filename = "config/settings.php";

if ($action == "install") {
    $setup_contents = '<?php
$database_host = "' . @$_POST['database_host'] . '";
$database_user = "' . @$_POST['database_user'] . '";
$database_password = "' . @$_POST['database_password'] . '";
$database_name = "' . @$_POST['database_name'] . '";
?>';

$f = fopen($filename, 'w');
if (!$f) {
    echo "<h2>can't write to file '$filename'</h2>Make sure you have the right  directory permissions";
    die;
}
fwrite($f, $setup_contents);
fclose($f);

require_once("includes/dbconnection.php");
$queries_text = file_get_contents("install/create_db.sql");
$queries = explode(';', $queries_text);

?><p style="font-family: courier; font-size: 10px;"><?php

$all_is_well = true;
mysql_query("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");
mysql_query("DROP DATABASE $database_name");
$all_is_well = execute_query_print_result("CREATE DATABASE $database_name CHARACTER SET utf8 COLLATE utf8_unicode_ci;
") && $all_is_well;
mysql_select_db($database_name);
foreach ($queries as $query) {
    $all_is_well = execute_query_print_result($query) && $all_is_well;
}

?></p><?php

if ($all_is_well) {
    echo '<h2>Installation Succesful</h2><a href="index.php">Start using Tabbie</a>';
} else echo '<h2>Installation Failed</h2>Make sure you have the right Database  permissions';

} else {
    if (file_exists($filename)) {
        echo "<h3>Warning - Tabbie is already installed. Executing this procedure again will erase all data</h3>";
    }
?>
<p>
Please fill out the form below. If you have no idea what these options mean - just click on the install button. Users that try to reinstall here: Please be aware of the fact that any existing data in the database indicated below will be overwritten.
</p>

<form action="install.php" method="POST">
    <input type="hidden" name="action" value="install"/>
    <input type="text" size="30" name="database_host" value="localhost"> Database Host<br/>
    <input type="text" size="30" name="database_user" value="root"> Database User<br/>
    <input type="text" size="30" name="database_password" value=""> Database Password<br/>
    <input type="text" size="30" name="database_name" value="tabbie"> Database Name<br/>
    <input type="submit" value="Install"/>
</form>

<?php
}
?>

  </div>

<?php require('view/footer.php'); ?>
</body>
</html>
