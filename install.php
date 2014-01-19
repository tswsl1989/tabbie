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
echo "<h2>Tabbie - Installation</h2>";

$action="";
if(array_key_exists("action", @$_POST)) $action=trim(@$_POST['action']);
$filename = "config/settings.php";

$reinstall = false;
$reinstall = file_exists($filename);

$messages=array();
if ($action == "install") {
	if ($reinstall) {
		$vars = array("database_user", "database_password", "database_name");
		foreach ($vars as $v) {
			$tmp  = get_old_config_val($filename, $v);
			if (trim($_POST[$v])=="" && $tmp) {
				$_POST[$v] = $tmp;
			}
		}
	}

	$all_is_well = write_config($filename);
	if ($all_is_well) {
		$all_is_well = include($filename);
	}

	if ($all_is_well) {
		$all_is_well = handle_image();
	}

	if ($all_is_well) {
		require_once("includes/dbconnection.php");
		$queries_text = file_get_contents("install/create_db.sql");
		$queries = explode(';', $queries_text);

		echo "<p style=\"font-family: courier; font-size: 10px;\">";

		$DBConn->Execute("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");
		$DBConn->Execute("DROP DATABASE $database_name;");
		if ($all_is_well) {
			$all_is_well = $DBConn->Execute("CREATE DATABASE $database_name CHARACTER SET utf8 COLLATE utf8_unicode_ci;") && $all_is_well;
			$all_is_well = $DBConn->Execute("USE $database_name") && $all_is_well;
			if (!$all_is_well) {
				$messages[] = "Unable to create and select database: ".$DBConn->ErrorMsg();
			} else {
				$messages[] = "Database created";
			}
		} 

		if ($all_is_well) {
			$l=0;
			foreach ($queries as $query) {
				if (trim($query)=="") {
					continue;
				}
				$all_is_well = $DBConn->Execute($query) && $all_is_well;
				$l++;
				if (!$all_is_well) {
					$messages[] = "DB Error at query $l: ".$DBConn->ErrorMsg();
				}
			}
		}

		if ($all_is_well) {
			$messages[] = "Database structure created";
		} else {
			$messages[] = "Failed to create database structures. Check SQL error messages";
		}

		if (file_exists("install/university.sql") && $all_is_well) {
			$queries = explode(";", file_get_contents("install/university.sql"));
			$u = true;
			foreach ($queries as $query) {
				$u = $DBConn->Execute($query) && $u;
			}
			if ($u) {
				$messages[]="University list successfully loaded";
			} else {
				$messages[]="Failed to load university list";
			}
		}
		echo "</p>";
	}

	if ($messages !== "") {
		echo "<div class=\"install_checks\"><ul>\n";
		foreach ($messages as $msg) {
			echo "<li>".$msg."</li>";
		}
		echo "</ul></div>";
	}

	if ($all_is_well) {
		echo '<h2>Installation Successful</h2><a href="index.php">Start using Tabbie</a>';
	} else {
		echo '<h2>Installation Failed</h2>Check error messages above';
	}

} else {
	echo "<div class=\"install_checks\">\n<ul>";
	echo "<strong>Checking requirements:</strong>";
	if ($reinstall) {
		echo "<li><span class=\"warn\">Warning</span> - Tabbie is already installed. Executing this procedure again will erase all data</li>\n";
	}
	if (!defined('PHP_VERSION_ID')) {
		$version = explode('.', PHP_VERSION);
		define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
	}
	$bail = 0;
	if (PHP_VERSION_ID < 50000) {
		echo "<li><span class=\"fail\">Fail</span> - PHP version 5.0 or greater required (".PHP_VERSION." found)</li>\n";
		$bail++;
	} else {
		echo "<li><span class=pass>Pass</span> - PHP version ".PHP_VERSION."</li>\n";
	}
	if (!ini_get("short_open_tags")) {
		if (PHP_VERSION_ID < 50400) {
			echo "<li><span class=fail>Fail</span> - PHP: 'short_open_tags' must be enabled in php.ini</li>\n";
			$bail++;
		} else {
			echo "<li><span class=pass>Pass</span> - PHP 'short_open_tags' disabled, but PHP version > 5.4.0</li>\n";
		}
	} else {
		echo "<li><span class=pass>Pass</span> - PHP: 'short_open_tags' enabled</li>\n";
	}

	if (extension_loaded("mbstring")) {
		echo "<li><span class=\"pass\">Pass</span> - PHP Multibyte string support loaded</li>\n";
	} else {
		echo "<li>span class=\"warn\">Warning</span> - PHP Multibyte string support not loaded<br />Foreign characters may be misprinted on ballots or cause other errors</li>\n";
	}

	if (is_dir("./config/")) {
		if (is_writable("./config/")) {
			echo "<li><span class=pass>Pass</span> - Config directory is writable</li>\n";
		} else {
			echo "<li><span class=fail>Fail</span> - Config directory read-only</li>\n";
			$bail++;
		}
	} else if (is_writable(".") && mkdir("./config/")) {
		echo "<li><span class=pass>Pass</span> - Config directory created</li>\n";
	} else {
		echo "<li><span class=fail>Fail</span> - Config directory does not exist and could not be created<br />Please create a directory called 'config' and make it writable by the webserver</li>\n";
		$bail++;
	}

	if (include("adodb/adodb.inc.php")) {
		echo "<li><span class=pass>Pass</span> - ADODB available</li>\n";
	} else {
		echo "<li><span class=fail>Fail</span> - ADODB not available</li>\n";
		$bail++;
	}

	echo "</ul></div>\n";

	if ($bail) {
		echo "<h3>Installation checks failed (".$bail." failures) - please fix these issues before continuing</h3>\n";
	} else {
		echo <<<FormTop

<form action="install.php" enctype="multipart/form-data" method="POST">
	<input type="hidden" name="action" value="install"/>
	<input type="hidden" name="MAX_FILE_SIZE" value="300000" /></td></tr>
	<table>
		<tr><td><label for="database_host">DB Host: </label></td><td><input type="text" size="30" name="database_host" value="localhost" /></td><td>Host for primary database. Almost always localhost</td></tr>
FormTop;
		if ($reinstall) {
			echo "\t\t<tr><td><label for=\"database_user\">DB User: </label></td><td><input type=\"text\" size=\"30\" name=\"database_user\" value=\"\" /></td><td>Leave blank to use current username</tr>";
			echo "\t\t<tr><td><label for=\"database_password\">DB Password: </label></td><td><input type=\"password\" size=\"30\" name=\"database_password\" value=\"\" /></td><td>Leave blank to use current password</td></tr>";
			echo "\t\t<tr><td><label for=\"database_name\">DB Name: </label></td><td><input type=\"text\" size=\"30\" name=\"database_name\" value=\"".get_old_config_val($filename, "database_name")."\" /></td><td>Account specified above must be able to create and delete tables in this database</td></tr>";
		} else {
			echo "\t\t<tr><td><label for=\"database_user\">DB User: </label></td><td><input type=\"text\" size=\"30\" name=\"database_user\" value=\"\" /></td>&nbsp;<td></tr>";
			echo "\t\t<tr><td><label for=\"database_password\">DB Password: </label></td><td><input type=\"password\" size=\"30\" name=\"database_password\" value=\"\" /></td><td>&nbsp;</td></tr>";
			echo "<tr><td><label for=\"database_name\">DB Name: </label></td><td><input type=\"text\" size=\"30\" name=\"database_name\" value=\"tabbie\" /></td><td>Account specified above must be able to create and delete tables in this database</td></tr>";
		}
		echo <<<FormBottom
		<tr><td><label for="local_name">Tournament Name:</label></td><td><input type="text" size="30" name="local_name" value="Tabbie" /></td><td>Tournament title to be displayed on menu screens and printouts</tr>
		<tr><td><label for="local_image">Tournament Logo: </label></td><td><input type="file" name="local_image" value=""></td><td><em>Optional</em> - As above</tr>
		<tr><td><label for="submit">Go!</label></td><td><input type="submit" value="Install"/></td><td>&nbsp;</td></tr>
	</table>
</form>
FormBottom;
	}
}

echo "</div>";
include('view/footer.php');
echo "</body>\n</html>";

function handle_image() {
	global $messages;
	if ($_FILES['local_image']['error'] == 0) {
		$im_str=file_get_contents($_FILES['local_image']['tmp_name']);
		$image=imagecreatefromstring($im_str);
		$x=imagesx($image);
		$y=imagesy($image);
		if($y>100) {
			$m=100/$y;
			$new_y=100;
			$new_x=$x*$m;
		}else{
			$new_x=$x;
			$new_y=$y;
		}
		$resized=imagecreatetruecolor($new_x,$new_y);
		imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_x, $new_y, $x, $y);
		if (imagepng($resized,"./config/local_logo.png")){
			$messages[]="Local logo resized and saved";
			return true;
		}else{
			$messages[]="Unable to save resized image";
			return false;
		}
	} elseif($_FILES['local_image']['error'] == 4) {
		$image_path="";
		return true;
	} else{
		$messages[]="Image Upload Failed with error code".$_FILES['local_image']['error'];
		return false;
	}
}

function write_config($filename) {
	global $messages;
	$setup_contents = "<?PHP\n";
	$setup_contents .= "\$database_host = '".$_POST['database_host']."';\n";
	$setup_contents .= "\$database_user = '".$_POST['database_user']."';\n";
	$setup_contents .= "\$database_password = '".$_POST['database_password']."';\n";
	$setup_contents .= "\$database_name = '".$_POST['database_name']."';\n";
	$setup_contents .= "\$local_name = '".$_POST['local_name']."';\n";
	if (file_exists("config/local_logo.png")) {
		$setup_contents .= "\$local_image ='config/local_logo.png';\n";
	}
	$setup_contents .= "?>";

	if (!is_writable($filename)) {
		$messages[]="Unable to write to file '$filename'. Check directory permissions";
		return false;
	}
	$f = fopen($filename, 'w');
	if (fwrite($f, $setup_contents) === false) {
		$messages[]="Failed to write to file '$filename'. Check permissions";
		return false;
	}
	fclose($f);
	return true;
}

function get_old_config_val($fname, $vname) {
	if (include($fname)) {
		# Including a file in a function restricts its scope
		# This means that variables defined in $fname are only visible here
		# If a variable exists with the name passed as $vname then its value is returned
		return isset($$vname) ? $$vname : false;
	} else {
		return false;
	}
}
?>
