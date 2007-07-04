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
?>