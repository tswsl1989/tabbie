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

require_once("includes/adjudicator.php");
require_once("includes/backend.php");
require_once("draw/adjudicator/simulated_annealing_config.php");

//for information on what simulated annealing is, see: http://en.wikipedia.org/wiki/Simulated_annealing

/*
TODO

add check at the end for common problems (double adjudicators for exemple)

**************beginning of december breaking point****************

out of panel-size-bounds as an extra panel size option

focus on high number problems first


everything that is related to probability of making the break / winning

bin protection

allow for a report on manual changes too (score and messages)
technical: weave in messaging and scoring mechanisms.

introduce 'geography' (i.e. debates with similar points) into random_select?

further tuning of the SA algorithm
further optimizing (speed) of the SA algorithm
*/


//Simulated Annealing:
$RUNS = 20000;
$UPHILL_PROBABILITY = 0.5;
$ALPHA = 0.0005;
$DETERMINATION = 500; // amount of times the algorithm searches for a better solution, before restarting at the best solution so far.

//Display Setting:
$MESSAGE_TRESHHOLD = 0;

function print_debug() {
	print "<pre>".print_r(debug_backtrace(), 1)."</pre>";
}

function occurrences_of($value, $array) {
    $result = 0;
    foreach ($array as $e)
        if ($e == $value)
            $result += 1;
    return $result;
}

$adjudicator_history = array();
function adjudicators_met($one, $two) {
    global $adjudicator_history;
    if (!isset($adjudicator_history[$one]))
        $adjudicator_history[$one] = array_count_values(get_co_adjudicators($one));
	if(!isset($adjudicator_history[$one][$two])) @$adjudicator_history[$one][$two]=0;
    return @$adjudicator_history[$one][$two];
}

$adjudicator_team_history = array();
function adjudicator_met_team($adjudicator, $team) {
    global $adjudicator_team_history;
    if (!isset($adjudicator_team_history[$adjudicator]))
        $adjudicator_team_history[$adjudicator] = array_count_values(get_adjudicator_met_teams($adjudicator));
	if(!isset($adjudicator_team_history[$adjudicator][$team])) @$adjudicator_team_history[$adjudicator][$team]=0;
    return @$adjudicator_team_history[$adjudicator][$team];
}

function format_dec($nr) {
    return sprintf("%01.1f", $nr);
}

function get_average(&$list, $attr) {
    $sum = 0;
    foreach ($list as &$item)
        $sum += $item[$attr];
    return $sum / count($list);
}

function set_desired_averages(&$debates, $average) {
    foreach ($debates as &$debate)
        $debate['desired_average'] = $average;
}

function set_unequal_desired_averages(&$debates, &$adjudicators) {
    global $scoring_factors;
    $average_adjudicator = get_average($adjudicators, 'ranking');
    $average_debate = get_average($debates, 'points');
    foreach ($debates as &$debate) {
        if ($average_debate == 0)
            $debate['desired_average'] = $average_adjudicator;
        else
            $debate['desired_average'] = $average_adjudicator * (1 - $scoring_factors['panel_steepness']) +
                ($average_adjudicator * $scoring_factors['panel_steepness'] * $debate['points'] / $average_debate);
    }
}

function cmp_debate_desc($one, $two) {
    $result = $two['points'] - $one['points'];
    if ($result == 0)
        $result = $two['debate_id'] - $one['debate_id'];
    return $result;
}

function sort_debates(&$debates) {
    usort($debates, "cmp_debate_desc");
}

function __set_debate(&$debates, $id, $field, $value) {
    foreach ($debates as &$debate)
        if ($debate['debate_id'] == $id)
            $debate[$field] = $value;
}

function set_ciaran_desired_chairs(&$debates) {
    $chairs = get_active_adjudicators('ranking');
    $prev_points = "init-value";
    $lo = 9999;
    $hi = 0;
    $todo = array();

    foreach ($debates as &$debate) {
        $points = $debate['points'];
        if ($points != $prev_points) {
            foreach ($todo as $debate_id) {
                __set_debate($debates, $debate_id, 'ciaran_chair_hi', $hi);
                __set_debate($debates, $debate_id, 'ciaran_chair_lo', $lo);
            }
            $prev_points = $points;
            $lo = 9999;
            $hi = 0;
            $todo = array();
        }

        $chair = array_pop($chairs);
        $ranking = $chair['ranking'];
        $lo = min($ranking, $lo);
        $hi = max($ranking, $hi);
        $todo[] = $debate['debate_id'];
    }
    foreach ($todo as $debate_id) {
        __set_debate($debates, $debate_id, 'ciaran_chair_hi', $hi);
        __set_debate($debates, $debate_id, 'ciaran_chair_lo', $lo);
    }
}

function set_desired_panel_sizes(&$debates, $adjudicator_count) {
    global $min_panel_size, $max_panel_size;
    $min_panel_size = 999;
    $max_panel_size = 0;
    $i = 0;
    $base = floor($adjudicator_count / count($debates));
    $break_point = $adjudicator_count % count($debates); 
    foreach ($debates as &$debate) {
        $extra = $i < $break_point ? 1 : 0;
        $debate['desired_panel_size'] = $base + $extra;
        $min_panel_size = min($min_panel_size, $debate['desired_panel_size']);
        $max_panel_size = max($max_panel_size, $debate['desired_panel_size']);
        $i++;
    }
}

function diff_to_bounds($n, $lo, $hi) {
    if ($n >= $lo && $n <= $hi) return 0;
    if ($n < $lo) return $lo - $n;
    if ($n > $hi) return $n - $hi;
}

function set_target_values(&$debates) {
    sort_debates($debates);
    $adjudicators = get_active_adjudicators();
    set_unequal_desired_averages($debates, $adjudicators);
    set_ciaran_desired_chairs($debates);
    set_desired_panel_sizes($debates, count($adjudicators));
}

function reallocate_simulated_annealing() {
    mt_srand(0);
    $nextround = get_num_rounds() + 1;
    $debates = debates_from_temp_draw_no_adjudicators($nextround);
    
    sort_debates($debates);
	$tmp001=get_active_adjudicators($order_by="ranking"); //Avoids an "only variables should be passed by reference" error
    initial_distribution($debates, $tmp001);
    set_target_values($debates);

    __do_a_run($debates, $nextround);
}

function refine_simulated_annealing(&$msg) {
    $nextround = get_num_rounds() + 1;
    $debates = debates_from_temp_draw_with_adjudicators($nextround);
    sort_debates($debates);

    set_target_values($debates);
    $energy = format_dec(debates_energy($debates));
    $msg[] = "Adjudicator Allocation (SA) score was: $energy (the closer to zero, the better)";

    __do_a_run($debates, $nextround);
}

function __do_a_run(&$debates, $nextround) {
    set_target_values($debates);
    debates_energy($debates); // sets caches
    actual_sa($debates);
    write_to_db($debates, $nextround);
}

function display_sa_energy(&$msg, &$details) {
    $nextround = get_num_rounds() + 1;
    $debates = debates_from_temp_draw_with_adjudicators($nextround);
    sort_debates($debates);
    $adjudicators = get_active_adjudicators();

    set_target_values($debates);
    $energy = format_dec(debates_energy($debates));
    $msg[] = "Adjudicator Allocation (SA) score is: $energy (the closer to zero, the better)";
    $details = debates_energy_details($debates);
}

function cmp_ranking($adjudicator_0, $adjudicator_1) {
    return $adjudicator_0['ranking'] - $adjudicator_1['ranking'];
}

function validate_adjudicators($debates) {
  $valid=true;
  $allocated_judges= array();
  foreach ($debates as $debate) {
    foreach ($debate['adjudicators'] as $adjud) {
      $allocated_judges[] = $adjud['adjud_id'];
    }
  }
  sort($allocated_judges);
  $prev_id=-1;
  foreach ($allocated_judges as $judge_id) {
    if ($judge_id==$prev_id) {
      $valid=false;
      break;
    }
    $prev_id=$judge_id;
  }
  return $valid;
}

function write_to_db($debates, $round) {
    //add some checks here...
  if (validate_adjudicators($debates)) {
    create_temp_adjudicator_table($round);
    foreach ($debates as &$debate) {
        usort($debate['adjudicators'], 'cmp_ranking');
        $chair = array_pop($debate['adjudicators']);
		$query="INSERT INTO `temp_adjud_round_$round` (`debate_id`, `adjud_id`, `status`) VALUES ('{$debate['debate_id']}','{$chair['adjud_id']}','chair')";
        if(!mysql_query($query)){
			echo mysql_error();
		}
        foreach ($debate['adjudicators'] as $adjudicator) {
			$query="INSERT INTO `temp_adjud_round_$round` (`debate_id`, `adjud_id`, `status`) VALUES ('{$debate['debate_id']}','{$adjudicator['adjud_id']}','panelist')";
            if(!mysql_query($query)){
				echo mysql_error();
			}
		}
    }
  }
      else {
	echo "<b>Error in Simulated annealing, adjudication failed validity test!</b><br>";
      }
}

function initial_distribution(&$debates, &$adjudicators) {
    $nr_debates = count($debates);
    $i = 0;
    while($adjudicator = array_pop($adjudicators)) {
        $debates[$i % $nr_debates]['adjudicators'][] = $adjudicator;
        $i++;
    }
}

function debate_energy(&$debate) {
    global $scoring_factors, $min_panel_size, $max_panel_size;
    $result = 0;
    $other_adjudicators = array_reverse($debate['adjudicators']);
    foreach ($debate['adjudicators'] as $adjudicator) {
		//echo("Scoring adjudicator ".$adjudicator['adjud_id']."<br/>");
        foreach ($adjudicator['univ_conflicts'] as $conflict){
 			//echo("Checking university conflict ".$conflict."<br/>");
            foreach ($debate['universities'] as $university){
				//echo("Against university in debate ".$university."<br/>");
				//echo("Checking conflict $conflict against university $university for ajudicator ".$adjudicator['adjud_id']);
                if ($conflict == $university) {
					//echo("STRIKE");
                    $result += $scoring_factors['university_conflict'];
                }
				//echo("<br/>");
			}
		}
        foreach ($adjudicator['team_conflicts'] as $conflict){
            foreach ($debate['teams'] as $team){
                if ($conflict == $team) {
                    $result += $scoring_factors['team_conflict'];
                }
			}
		}
        //echo("<br/>");
        array_pop($other_adjudicators);
        foreach ($other_adjudicators as $other_adjudicator)
            $result += adjudicators_met($adjudicator['adjud_id'], $other_adjudicator['adjud_id']) *  $scoring_factors['adjudicator_met_adjudicator'];
        foreach ($debate['teams'] as $team_id) {
            $result += adjudicator_met_team($adjudicator['adjud_id'], $team_id) * $scoring_factors['adjudicator_met_team'];
        }
    }
    
    $adjudicators = $debate['adjudicators'];
    usort($adjudicators, 'cmp_ranking');
    $chair = array_pop($adjudicators);
	if($chair['status']=='trainee') $result += $scoring_factors['trainee_in_chair'];
	if($chair['status']=='watched') $result += $scoring_factors['watched_not_watched'];
		
	foreach($adjudicators as $panelist){
		if($panelist['status']=='watcher') $result += $scoring_factors['watcher_not_in_chair'];
		if(($panelist['status']=='watched')&&($chair['status']!='watcher')) {
			$result += $scoring_factors['watched_not_watched'];
			}
	}
    $result += $scoring_factors['chair_not_perfect'] * (100 - $chair['ranking']);
    $result += $scoring_factors['chair_not_ciaran_perfect'] * pow(diff_to_bounds($chair['ranking'], $debate['ciaran_chair_lo'], $debate['ciaran_chair_hi']), 2);

    $result += $scoring_factors['panel_strength_not_perfect'] * pow(get_average($debate['adjudicators'], 'ranking') - $debate['desired_average'], 2);

    $panel_size = count($debate['adjudicators']);
    $result += $scoring_factors['panel_size_not_perfect'] * pow($debate['desired_panel_size'] - $panel_size, 2);

    $result += $scoring_factors['panel_size_out_of_bounds'] * pow(diff_to_bounds($panel_size, $min_panel_size, $max_panel_size), 2);

    return $result;
}

function debate_energy_details(&$debate) {
    global $scoring_factors, $min_panel_size, $max_panel_size;
    $result = array();

    $other_adjudicators = array_reverse($debate['adjudicators']);
    foreach($debate['adjudicators'] as $adjudicator) {
        foreach($adjudicator['univ_conflicts'] as $conflict) 
            foreach ($debate['universities'] as $university)
                if ($conflict == $university) {
                    $x = get_university($university);
                    $university_code = $x['univ_code'];
                    $result[] = array($scoring_factors['university_conflict'], "{$adjudicator['adjud_name']} has a conflict with university '$university_code'");
                }
        foreach($adjudicator['team_conflicts'] as $conflict) 
            foreach ($debate['teams'] as $team)
                if ($conflict == $team) {
                    $result[] = array($scoring_factors['team_conflict'], "{$adjudicator['adjud_name']} has a conflict with team_id '$conflict'");
                }
        array_pop($other_adjudicators);
        foreach ($other_adjudicators as $other_adjudicator) {
            $occurrences = adjudicators_met($adjudicator['adjud_id'], $other_adjudicator['adjud_id']);
            $penalty = $occurrences * $scoring_factors['adjudicator_met_adjudicator'];
            if ($occurrences)
                $result[] = array($penalty, "{$adjudicator['adjud_name']} met {$other_adjudicator['adjud_name']} $occurrences time(s) before.");
        }
        foreach ($debate['teams'] as $team_id) {
            $occurrences = adjudicator_met_team($adjudicator['adjud_id'], $team_id);
            $penalty = $occurrences * $scoring_factors['adjudicator_met_team'];
            $team_name = team_name($team_id);
            if ($occurrences)
                $result[] = array($penalty, "{$adjudicator['adjud_name']} met '$team_name' $occurrences time(s) before.");
        }
    }
    
    $adjudicators = $debate['adjudicators'];
    usort($adjudicators, 'cmp_ranking');
    $chair = array_pop($adjudicators);

	if($chair['status']=='trainee') {
		$result[] = array($scoring_factors['trainee_in_chair'], "Trainee is in the chair.");
		}
	if($chair['status']=='watched') {
		$result[]=array($scoring_factors['watched_not_watched'], "Adjudicator marked to be watched is not being watched.");
		}
	foreach($adjudicators as $panelist){
		if($panelist['status']=='watcher') {
			$result[]=array($scoring_factors['watcher_not_in_chair'], "Adjudicator marked capable of watching is not in the chair.");
			}
		if(($panelist['status']=='watched')&&($chair['status']!='watcher')) {
			$result[]=array($scoring_factors['watched_not_watched'], "Adjudicator marked to be watched is not being watched.");
			}
	}

    $diff = 100 - $chair['ranking'];
    $penalty = $scoring_factors['chair_not_perfect'] * ($diff);
    $diff = format_dec($diff);
    $result[] = array($penalty, "Chair {$chair['adjud_name']} has $diff difference from 100.0.");

    $diff = diff_to_bounds($chair['ranking'], $debate['ciaran_chair_lo'], $debate['ciaran_chair_hi']);
    $penalty = pow($diff, 2) * $scoring_factors['chair_not_ciaran_perfect'];
    $diff = format_dec($diff);
    $lo = format_dec($debate['ciaran_chair_lo']);
    $hi = format_dec($debate['ciaran_chair_hi']);
    $result[] = array($penalty, "Desired 'Ciaran Ideal Bounds' are [$lo, $hi], actual chair strength is $diff out of bounds.");

    $real = get_average($debate['adjudicators'], 'ranking');
    $desired_average = $debate['desired_average'];
    $penalty = format_dec($scoring_factors['panel_strength_not_perfect'] * pow($real - $desired_average, 2));
    $desired_average = format_dec($desired_average);
    $real = format_dec($real);
    $result[] = array("$penalty", "Desired average is $desired_average and real average is $real");

    $panel_size = count($debate['adjudicators']);
    $diff = abs($debate['desired_panel_size'] - $panel_size);
    $penalty = pow($diff, 2) * $scoring_factors['panel_size_not_perfect'];
    $result[] = array("$penalty", "Desired panel size is {$debate['desired_panel_size']} and real panel size is $panel_size");

    $diff = diff_to_bounds($panel_size, $min_panel_size, $max_panel_size);
    $penalty = pow($diff, 2) * $scoring_factors['panel_size_out_of_bounds'];
    $result[] = array("$penalty", "Desired panel bounds are [$min_panel_size, $max_panel_size] and real panel size is $panel_size");



    return $result;
}

function debates_energy(&$debates) {
    $result = 0;
    foreach ($debates as &$debate) {
        if (! isset($debate['energy'])) {
            $debate['energy'] = debate_energy($debate);
        }
        $result += $debate['energy'];
    }
    return $result;
}

function debates_energy_details(&$debates) {
    global $MESSAGE_TRESHHOLD;
    $result = array();
    foreach ($debates as $debate) {
        $total = 0;
        foreach (debate_energy_details($debate) as $detail) {
            $result[$debate['debate_id']][] = $detail;
            $total += $detail[0];
        }
        $energy = debate_energy($debate);
        if ($total - $energy > 0.5) {
            $result[$debate['debate_id']][] = array(66666, "There seems to be a problem with the algorithm: $total != $energy");
        }
    }
    return $result;
}

function random_select(&$debates, $long_scope=0) {
    $debate_index = mt_rand(0, count($debates) - 1);
    $debate = $debates[$debate_index];
    $adjudicator_index = mt_rand(0, count($debate['adjudicators']) - 1 + $long_scope);
    return array($debate_index, $adjudicator_index);
}

function swap(&$debates, &$one, &$two) {
    if ($two[1] >= count($debates[$two[0]]['adjudicators'])) {
        $debates[$two[0]]['adjudicators'][$two[1]] = $debates[$one[0]]['adjudicators'][$one[1]];

        $last_adjudicator_index = count($debates[$one[0]]['adjudicators']) - 1;
        $debates[$one[0]]['adjudicators'][$one[1]] = $debates[$one[0]]['adjudicators'][$last_adjudicator_index];
        unset($debates[$one[0]]['adjudicators'][$last_adjudicator_index]);
        //one is adapted to be able to undo the swap...:
        $one[1] = $last_adjudicator_index;
    } else {
        $buffer = $debates[$one[0]]['adjudicators'][$one[1]];
        $debates[$one[0]]['adjudicators'][$one[1]] = $debates[$two[0]]['adjudicators'][$two[1]];
        $debates[$two[0]]['adjudicators'][$two[1]] = $buffer;
    }
}

function actual_sa(&$debates) {
    global $RUNS, $DETERMINATION;
    $temp = 1.0;
    $best_energy = debates_energy($debates);
    $best_debates = $debates;
    $best_moment = 0;
    $i = 0;
    if (count($debates) >= 2) {
        while ($i < $RUNS) {
            do {
                $one = random_select($debates);
                $move_possible = count($debates[$one[0]]['adjudicators']) > 1 ? 1 : 0;
                $two = random_select($debates, $move_possible);
            } while ($one[0] == $two[0]);	
    
            $before = $debates[$one[0]]['energy'] + $debates[$two[0]]['energy'];
            swap($debates, $one, $two);
            $after = debate_energy($debates[$one[0]]) + debate_energy($debates[$two[0]]);
            $diff = $after - $before;
    
            if (!throw_dice(probability($diff, $temp))) {
                swap($debates, $two, $one); //swap back
            } else {
                $debates[$one[0]]['energy'] = debate_energy($debates[$one[0]]);
                $debates[$two[0]]['energy'] = debate_energy($debates[$two[0]]);
            }
    
            if ($diff < 0) { //better than prev
                $energy = debates_energy($debates);
                if ($energy < $best_energy) {
                    $best_debates = $debates;
                    $best_energy = $energy;
                    $best_moment = $i;
                }
            } elseif ($i - $best_moment > $DETERMINATION) {
                $best_moment = $i;
                $debates = $best_debates;
            }
            $temp = decrease_temp($temp);
            $i++;
        }
    }
    $debates = $best_debates;
}

function probability($diff, $temp) {
    global $UPHILL_PROBABILITY;
    if ($diff <= 0)
        return 1;
    if ($diff > 0)
        return $temp * $UPHILL_PROBABILITY;
}

function throw_dice($probability) {
    $nr = 10000;
    $dice = mt_rand(0, $nr - 1);
    return ($dice <= $probability * $nr);
}

function decrease_temp($temp) {
    global $ALPHA;
    return $temp * (1 - $ALPHA);
}


?>
