<?php
function q($query) {
    $result = mysql_query($query);
    $error = mysql_error();
    if ($error)
        throw new Exception("Error in query [$query]:" .  $error);
    return $result;
}
?>