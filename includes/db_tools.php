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

function q($query) {
    global $DBConn;
    ini_set('display_errors','1');
    $result = $DBConn->Execute($query);
    if (!$result) {
        $error = $DBConn->ErrorMsg();
        throw new Exception("Error in query ".$error." [$query]");
    }
    return $result;
}

function qp($query, $params) {
    global $DBConn;
    $result = $DBConn->Execute($query, $params);
    if (!$result) {
        $error = $DBConn->ErrorMsg();
        throw new Exception("Error in query: ".$error." [$query, ".print_r($params, 1)."]");
    }
    return $result;
}

function count_rows($table, $where_clause = "1 = 1") {
    $query = "SELECT * FROM $table WHERE $where_clause";
    $result = q($query);
    $rc = $result->RecordCount();
    return $rc;
}

function get_db_error() {
    global $DBConn;
    return $DBConn->ErrorMsg();
}
?>
