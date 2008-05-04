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
require_once("includes/backend.php");

$ntu_controller = "print"; #selected in menu
$title = "Motion Fairness";
require("view/header.php");
require("view/mainmenu.php");

$POSITIONS = array('og' => "Opening Government", 'oo' => "Opening Opposition", 'cg' => "Closing Government", 'co' => "Closing Opposition");
$INFOS = array('first', 'second', 'third', 'fourth', 'normalized');

for ($round = 1; $round <= get_num_completed_rounds(); $round++) {
    $motion = get_motion_for_round($round);
    print "<h2>Round $round</h2>";
    print "<h3>Motion: $motion</h3><table><tr>";
    $results = results_by_position($round);
    foreach ($POSITIONS as $short => $long) {
        print "<td>";
        print "<h3>$long</h3>";
        @$totals[$short] += $results[$short]["total"];
        @$total += $results[$short]["total"];
        foreach ($INFOS as $info) {
            print ucwords($info) . ": " . $results[$short][$info] . "<br>";
        } 
        print "</td>";
    }
    print "</tr></table>";
}

print "<h2>Entire Tournament</h2>";
print "<table><tr>";
foreach ($POSITIONS as $short => $long) {
    print "<td>";
    print "<h3>$long</h3>";
    print "Normalized: " . sprintf("%001d", $totals[$short] / $total * 400) . "%<br>";
    print "</td>";
}
print "</tr></table>";


require("view/footer.php");

?>