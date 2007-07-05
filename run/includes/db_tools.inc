<?php
function q($query) {
    $result = mysql_query($query);
    $error = mysql_error();
    if ($error) {
        $version = phpversion();
        if ($version[0] == '5') 
            eval('throw new Exception("Error in query [$query]:" .  $error);');
        else {
            print "Error in query [$query]:" .  $error;
            die;
        }
    }
    return $result;
}

function count_rows($table, $where_clause = "1 = 1") {
    $query = "SELECT COUNT(*) FROM $table WHERE $where_clause";
    $result = q($query);
    $row = mysql_fetch_row($result);
    return $row[0];
}
?>