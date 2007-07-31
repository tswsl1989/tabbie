<?php
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
