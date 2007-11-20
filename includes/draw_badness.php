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

//This file is generated automatically by draw.py. Editing it manually is considered very stupid

$badness_lookup = array(
    "0, 0, 0, 0" => 0,
    "0, 0, 0, 1" => 0,
    "0, 0, 0, 2" => 4,
    "0, 0, 0, 3" => 36,
    "0, 0, 0, 4" => 144,
    "0, 0, 0, 5" => 324,
    "0, 0, 0, 6" => 676,
    "0, 0, 0, 7" => 1296,
    "0, 0, 0, 8" => 2304,
    "0, 0, 0, 9" => 3600,
    "0, 0, 1, 1" => 0,
    "0, 0, 1, 2" => 4,
    "0, 0, 1, 3" => 36,
    "0, 0, 1, 4" => 100,
    "0, 0, 1, 5" => 256,
    "0, 0, 1, 6" => 576,
    "0, 0, 1, 7" => 1156,
    "0, 0, 1, 8" => 1936,
    "0, 0, 2, 2" => 16,
    "0, 0, 2, 3" => 36,
    "0, 0, 2, 4" => 100,
    "0, 0, 2, 5" => 256,
    "0, 0, 2, 6" => 576,
    "0, 0, 2, 7" => 1024,
    "0, 0, 3, 3" => 64,
    "0, 0, 3, 4" => 144,
    "0, 0, 3, 5" => 324,
    "0, 0, 3, 6" => 576,
    "0, 0, 4, 4" => 256,
    "0, 0, 4, 5" => 400,
    "0, 1, 1, 1" => 0,
    "0, 1, 1, 2" => 4,
    "0, 1, 1, 3" => 16,
    "0, 1, 1, 4" => 64,
    "0, 1, 1, 5" => 196,
    "0, 1, 1, 6" => 484,
    "0, 1, 1, 7" => 900,
    "0, 1, 2, 2" => 4,
    "0, 1, 2, 3" => 16,
    "0, 1, 2, 4" => 64,
    "0, 1, 2, 5" => 196,
    "0, 1, 2, 6" => 400,
    "0, 1, 3, 3" => 36,
    "0, 1, 3, 4" => 100,
    "0, 1, 3, 5" => 196,
    "0, 1, 4, 4" => 144,
    "0, 2, 2, 2" => 4,
    "0, 2, 2, 3" => 16,
    "0, 2, 2, 4" => 64,
    "0, 2, 2, 5" => 144,
    "0, 2, 3, 3" => 36,
    "0, 2, 3, 4" => 64,
    "0, 3, 3, 3" => 36,
    "1, 1, 1, 1" => 0,
    "1, 1, 1, 2" => 0,
    "1, 1, 1, 3" => 4,
    "1, 1, 1, 4" => 36,
    "1, 1, 1, 5" => 144,
    "1, 1, 1, 6" => 324,
    "1, 1, 2, 2" => 0,
    "1, 1, 2, 3" => 4,
    "1, 1, 2, 4" => 36,
    "1, 1, 2, 5" => 100,
    "1, 1, 3, 3" => 16,
    "1, 1, 3, 4" => 36,
    "1, 2, 2, 2" => 0,
    "1, 2, 2, 3" => 4,
    "1, 2, 2, 4" => 16,
    "1, 2, 3, 3" => 4,
    "2, 2, 2, 2" => 0,
    "2, 2, 2, 3" => 0
);

function badness($positions) {
    global $badness_lookup;
    sort($positions);
    
    //temp. hack - assuming that 10+ round tournaments with 0-round distributions are *very* rare and 
    //that this table will be replaced by the actual code before...
    while ($positions[0] + $positions[1] + $positions[2] + $positions[3] >= 10) {
        for ($i = 0; $i < 4; $i++)
            $positions[$i] = max(0, $positions[$i] - 1);
    }
    return $badness_lookup["{$positions[0]}, {$positions[1]}, {$positions[2]}, {$positions[3]}"];
}

?>
