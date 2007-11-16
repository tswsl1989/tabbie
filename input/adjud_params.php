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
require_once("draw/adjudicator/simulated_annealing_config.php");
require("includes/display.php");

$submited=trim(@$_POST['save']);
if ($submited=="submitted") {
  foreach ($scoring_factors as $pname=>$pvalue) {
    $scoring_factors[$pname]=trim(@$_POST["param_".$pname]);
  }
  store_scoring_factors_to_db($scoring_factors);
}

echo "<h2>Adjust adjudicator allocation parameters</h2>\n"; //title
echo "<form action=\"input.php?moduletype=adjud_params\" method=POST><table border=1><tr><th>Parameter name</th><th>parameter value</th></tr>";
//iterate over params:
foreach ($scoring_factors as $pname => $pvalue) {
  printf("<tr><td>%s</td><td><input name=\"param_%s\" value=\"%d\"></td></tr>\n",$pname,$pname,$pvalue);
}
printf("</table>");
//echo "<input type=\"hiddedn\" value=\"adjud_params\">";
echo "<button name=\"save\" type=\"submit\" value=\"submitted\">save</button>";

?>
