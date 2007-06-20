<?
require_once("config/settings.php");

mysql_connect($database_host, $database_user, $database_password);
mysql_select_db($database_name);

?>