<?
//Make connection to database
mysql_connect("localhost", "root", "");
mysql_select_db("tabbie");

$query="SELECT univ_name,univ_code FROM university ORDER BY univ_name";
$result=mysql_query($query);
?>
<table  name="teamlist" border="1" width="100%">
<?
	while($row=mysql_fetch_assoc($result))
	{
		$univ_name=$row['univ_name'];
		$univ_code=$row['univ_code'];

		echo "<tr><td width=\"160\">$univ_name</td><td width=\"142\">$univ_code</td></tr>";
	}
?>
</table>
