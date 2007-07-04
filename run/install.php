<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title>Install Tabbie</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="style.css">
</head>

<body>
  <h1 id="main">Tabbie - British Parliamentary Debating Tabbing Software</h1>

  <div id="content">
    <h2>Tabbie - British Parliamentary Debating Tabbing Software</h2>

<?php
$action=@$_POST['action'];
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
$queries = split(';', $queries_text);

?><p style="font-family: courier; font-size: 10px;"><?php

function execute_query_print_result($query) {
    if (trim($query)) {
        $result = mysql_query($query);
        if (!$result) {
            $error = mysql_error();
            print "<b>$query => FAIL: $error</b><br>";
            return false;
        } else
            print "$query => SUCCESS<br>";
    }
    return true;
}

$all_is_well = true;
mysql_query("DROP DATABASE $database_name");
$all_is_well = execute_query_print_result("CREATE DATABASE $database_name") && $all_is_well;
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

<?php include('customize/footer.inc'); ?>
</body>
</html>