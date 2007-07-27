<?
require_once("includes/backend.php");

$result = get_adjudicators_venues(3);

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
} elseif (@$_REQUEST["result_type"] == "html") {
    print "<table>";
    print "<tr><th>" . join("</th><th>", $result["header"]) . "</th></tr>\n";
    foreach ($result["data"] as $row) {
        print "<tr><th>" . join("</th><th>", $row) . "</th></tr>\n";
    }
    print "</table>";
} else {
    print_r($result);
}

?>