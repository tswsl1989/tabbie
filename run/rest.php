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
require_once("includes/adjudicator_sheets_pdf.php");

$function = @$_REQUEST["function"];
$param = @$_REQUEST["param"];

$result = eval("return $function($param);");

if (@$_REQUEST["result_type"] == "txt") {
    header('Content-Type: text/plain');
    print join(" ", $result["header"]) . "\n";
    foreach ($result["data"] as $row) {
        print join(" ", $row) ."\n";
    }
} elseif (@$_REQUEST["result_type"] == "css") {
    header('Content-Type: text/css');
    print join(",", $result["header"]) . "\n";
    foreach ($result["data"] as $row) {
        print join(",", $row) ."\n";
    }
} elseif (@$_REQUEST["result_type"] == "pdf") {
    eval("{$function}_pdf('{$function}_round_$param.pdf', " . '$result["data"]' . ");");
} elseif (@$_REQUEST["result_type"] == "html") {
    $ntu_controller = "print"; #selected in menu
    $title = @$_REQUEST["title"];
    
    require("view/header.php");
    require("view/mainmenu.php");

    print "<h3>$title</h3>";
    print "<table>";
    print "<tr><th>" . join("</th><th>", $result["header"]) . "</th></tr>\n";
    foreach ($result["data"] as $row) {
        print "<tr><td>" . join("</td><td>", $row) . "</td></tr>\n";
    }
    print "</table>";
    require('view/footer.php'); 
} else {
    print_r($result);
}

?>
