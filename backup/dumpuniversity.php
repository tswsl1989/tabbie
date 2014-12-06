<?PHP
set_include_path("..:".get_include_path());
require_once("../includes/dbconnection.php");

date_default_timezone_set(@date_default_timezone_get());

$date = date("Y-m-d");
$time = date("H:i:s O");
# Get hostname from PHP - using the proper function if PHP is new enough, cheating if not 
$host = PHP_VERSION_ID < 50300 ? php_uname('n') : gethostname();
$header = <<<EoH
-- Tabbie Database Dump
-- Generated on $date at $time
-- Generated from database $database_name on $host

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
EoH;

$footer = <<<EoF
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- End of Tabbie Database Dump
EoF;
 
$rs = $DBConn->Execute("SHOW CREATE TABLE university");
if (!$rs) {
	die("Unable to get CREATE TABLE statement for a table (university)");
}

$dump[] = "DROP TABLE IF EXISTS university;\n".$rs->FetchRow()[$rs->FetchField(1)->name];

$rs = $DBConn->Execute("SELECT * FROM university");
if (!$rs) {
	die("Unable to get data from table (university)");
}
$ins=array();
$pre="\nLOCK TABLES university WRITE;\n";
$pre.="/*!40000 ALTER TABLE university DISABLE KEYS */;\n";
$post="\n/*!40000 ALTER TABLE university ENABLE KEYS */;\n";
$post.="UNLOCK TABLES\n";
while ($row = $rs->FetchRow()) {
	$ins[]=$DBConn->GetInsertSQL($rs, $row).";\n";
}
$itmp = count($ins) > 0 ? implode($ins) : "-- No data for university";
$dump[]=$pre.$itmp.$post;

$rs = $DBConn->Execute("SHOW WARNINGS");
if ($rs->RecordCount()>0) {
	while ($j=$rs->FetchRow()) {
		print $j;
	}
	die("Warnings encountered - bailing out");
}

header('Content-type: text/plain'); 
header('Content-Disposition: attachment; filename="university.sql"');
header('Content-Description: File Transfer');
print $header."\n";
print implode(";\n\n", $dump).";\n";
print $footer;
