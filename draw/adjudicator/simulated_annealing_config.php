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

require_once("includes/settings.php");

function ensure_scoring_factors_in_db() {
	trigger_error("Deprecated scoring_factors call made", E_USER_DEPRECATED);
	return ensure_settings_in_db();
}

function get_scoring_factors_from_db() {
	trigger_error("Deprecated scoring_factors call made", E_USER_DEPRECATED);
	ensure_settings_in_db();
	return store_settings_to_db();
}

function store_scoring_factors_to_db($params) {
	trigger_error("Deprecated scoring_factors call made", E_USER_DEPRECATED);
	return store_settings_to_db($params);
}
?>
