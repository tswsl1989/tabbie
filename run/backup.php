<?php
require_once("config/settings.php");
if ($database_password)
    $password = "-p$database_password";
else
    $password = "";
$command = "mysqldump -u$database_user $password $database_name";

$output = array();
$return_value = "undefined";

exec($command, &$output, &$return_value);

if ($return_value == 0) {
    header('Content-type: text/x-sql'); 
    header('Content-Disposition: attachment; filename="' . $database_name . '.sql"');
    foreach ($output as $line)
        print "$line\n";
} else {
?>
There was a problem executing the command '<?= $command?>', error code: <?= $return_value?><br>
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
<?
}
?>
