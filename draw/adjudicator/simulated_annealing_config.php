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

//Energy calculation:

function get_scoring_factors_from_db() {
  $params = array();
  $db_res = mysql_query("select param_name,param_value from configure_adjud_draw");
  while ($row = mysql_fetch_assoc($db_res)) {
    $params[$row['param_name']]=$row['param_value'];
  }
  return $params;
}

function store_scoring_factors_to_db($params) {
    foreach ($params as $param => $pvalue) {
      mysql_query("update configure_adjud_draw set param_value='$pvalue' where param_name='$param'");
  }
}

$scoring_factors = get_scoring_factors_from_db();
//store_scoring_factors_to_db($scoring_factors);

?>
