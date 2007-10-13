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
TODO for this file:
team conflicts (next to already existing university conflicts)

**** version 1.3 breaking point *****

everything that is related to probability of making the break / winning

bin protection

number of adjudicators per debate could vary

allow for a report on manual changes too (score and messages)
technical: weave in messaging and scoring mechanisms.

introduce 'geography' (i.e. debates with similar points) into random_select?

further tuning of the SA algorithm
further optimizing (speed) of the SA algorithm
*/

//Simulated Annealing:
$RUNS = 10000;
$UPHILL_PROBABILITY = 0.5;
$ALPHA = 0.0005;
$DETERMINATION = 500; // amount of times the algorithm searches for a better solution, before restarting at the best solution so far.

//Display Setting:
$MESSAGE_TRESHHOLD = 35;


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
    return @$adjudicator_history[$one][$two];
}

$adjudicator_team_history = array();
function adjudicator_met_team($adjudicator, $team) {
    global $adjudicator_team_history;
    if (!isset($adjudicator_team_history[$adjudicator]))
        $adjudicator_team_history[$adjudicator] = array_count_values(get_adjudicator_met_teams($adjudicator));
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

function allocate_simulated_annealing(&$msg, &$details) {
    $nextround = get_num_rounds() + 1;
    mt_srand(0);
    $debates = temp_debates_foobar($nextround);
    $adjudicators = get_active_adjudicators();
    set_unequal_desired_averages($debates, $adjudicators);
    initial_distribution($debates, $adjudicators);
    debates_energy($debates); // sets caches
    actual_sa($debates);
    $energy = format_dec(debates_energy($debates));
    $msg[] = "Adjudicator Allocation (SA) score is: $energy (the closer to zero, the better)";
    $details = debates_energy_details($debates);
    write_to_db($debates, $nextround);
}

function cmp_ranking($adjudicator_0, $adjudicator_1) {
    return $adjudicator_0['ranking'] - $adjudicator_1['ranking'];
}

function write_to_db($debates, $round) {
    //add some checks here...
    create_temp_adjudicator_table($round);
    foreach ($debates as &$debate) {
        usort($debate['adjudicators'], 'cmp_ranking');
        $chair = array_pop($debate['adjudicators']);
        mysql_query("INSERT INTO `temp_adjud_round_$round` " .
            "VALUES('{$debate['debate_id']}','{$chair['adjud_id']}','chair')");
        foreach ($debate['adjudicators'] as $adjudicator)
            mysql_query(
                "INSERT INTO `temp_adjud_round_$round` " .
                "VALUES('{$debate['debate_id']}','{$adjudicator['adjud_id']}','panelist')");
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
    global $scoring_factors;
    $result = 0;
    $other_adjudicators = array_reverse($debate['adjudicators']);
    foreach ($debate['adjudicators'] as $adjudicator) {
        foreach ($adjudicator['univ_conflicts'] as $conflict) 
            foreach ($debate['universities'] as $university)
                if ($conflict == $university) {
                    $result += $scoring_factors['university_conflict'];
                }
        foreach ($adjudicator['team_conflicts'] as $conflict) 
            foreach ($debate['teams'] as $team)
                if ($conflict == $team) {
                    $result += $scoring_factors['team_conflict'];
                }
        
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
    $result += $scoring_factors['chair_not_perfect'] * (100 - $chair['ranking']);

    $result += $scoring_factors['panel_strength_not_perfect'] * pow(get_average($debate['adjudicators'], 'ranking') - $debate['desired_average'], 2);

    return $result;
}

function debate_energy_details(&$debate) {
    global $scoring_factors;
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
    $diff = 100 - $chair['ranking'];
    $penalty = $scoring_factors['chair_not_perfect'] * ($diff);
    $diff = format_dec($diff);
    $result[] = array($penalty, "Chair {$chair['adjud_name']} has $diff difference from 100.0.");

    $real = get_average($debate['adjudicators'], 'ranking');
    $desired_average = $debate['desired_average'];
    $penalty = format_dec($scoring_factors['panel_strength_not_perfect'] * pow($real - $desired_average, 2));
    $desired_average = format_dec($desired_average);
    $real = format_dec($real);
    $result[] = array("$penalty", "Desired average is $desired_average but real average is $real");
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
    foreach ($debates as $debate)
        foreach (debate_energy_details($debate) as $detail)
            if ($detail[0] >= $MESSAGE_TRESHHOLD)
                $result[$debate['debate_id']][] = format_dec($detail[0]) . ": " . $detail[1];
    return $result;
}

function random_select(&$debates) {
    $i = mt_rand(0, count($debates) - 1);
    $debate = $debates[$i];
    $j = mt_rand(0, count($debate['adjudicators']) - 1);
    return array($i, $j);
}

function swap(&$debates, $one, $two) {
    $buffer = $debates[$one[0]]['adjudicators'][$one[1]];
    $debates[$one[0]]['adjudicators'][$one[1]] = $debates[$two[0]]['adjudicators'][$two[1]];
    $debates[$two[0]]['adjudicators'][$two[1]] = $buffer;
}

function actual_sa(&$debates) {
    global $RUNS, $DETERMINATION;
    $temp = 1.0;
    $best_energy = debates_energy($debates);
    $best_debates = $debates;
    $best_moment = 0;
    $i = 0;
    while ($i < $RUNS) {
        do {
            $one = random_select($debates);
            $two = random_select($debates);
        } while ($one[0] == $two[0]);
        $before = $debates[$one[0]]['energy'] + $debates[$two[0]]['energy'];
        swap($debates, $one, $two);
        $after = debate_energy($debates[$one[0]]) + debate_energy($debates[$two[0]]);
        $diff = $after - $before;
        if (!throw_dice(probability($diff, $temp))) {
            swap($debates, $one, $two); //swap back
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
