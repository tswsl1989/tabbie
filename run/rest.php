<?
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