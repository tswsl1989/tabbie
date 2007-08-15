<?
require_once("includes/adjudicator.php");
require_once("includes/backend.php");

//for information on what simulated annealing is, see: http://en.wikipedia.org/wiki/Simulated_annealing

/*
debate data structure:

universities in the debate
[later] teams in the debate

desired average
desired amount of adjudicators

actual adjudicators, ordered by score. First is chair
(computable: actual average)

probably: cached score
*/

/*
adjudicator data structure:
'ranking'
conflicting teams
previous co-adjudicators
previous teams
previous universities?
...

*/

function get_average_ranking(&$adjudicators) {
    $sum = 0;
    foreach ($adjudicators as $adjudicator)
        $sum += $adjudicator['ranking'];
    return $sum / count($adjudicators);
}

function set_desired_averages(&$debates, $average) {
    foreach ($debates as &$debate)
        $debate['desired_average'] = $average;
}

function allocate_simulated_annealing() {
    $nextround = get_num_rounds() + 1;
    srand(0);
    $debates = temp_debates_foobar($nextround);
    $adjudicators = get_active_adjudicators();
    set_desired_averages($debates, get_average_ranking($adjudicators));
    initial_distribution($debates, $adjudicators);
    print(debates_energy($debates));
    actual_sa($debates);
    print_r($debates);
    print(debates_energy($debates));
    //write_to_db();
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
    $result = 0;
    foreach($debate['adjudicators'] as $adjudicator)
        foreach($adjudicator['univ_conflicts'] as $conflict) 
            foreach ($debate['universities'] as $university)
                if ($conflict == $university) {
                    $result += 1000;
                    print "${adjudicator['adjud_id']} $conflict \n";
                }
    $result += 1 * abs(get_average_ranking($debate['adjudicators']) - $debate['desired_average']);
    return $result;
    
    /*
    based on:
    conflicts
    difference desired average - actual average
    'conflicts' for same people in panel.
    'conflicts' for adjudicating the same teams again
    'conflicts' for adjudicating the same uni again
    [later] this should be made configurable
    */
}

function debates_energy(&$debates) {
    $result = 0;
    foreach ($debates as $debate)
        $result += debate_energy($debate);
    return $result;
}

function random_select(&$debates) {
    $i = mt_rand(0, count($debates) - 1);
    $debate = $debates[$i];
    $j = mt_rand(0, count($debate['adjudicators']) - 1);
    return array($i, $j);
    /*
    take any adjudicator
    find neighbouring debate (and team), giving pref. to closer debates (at least dist 1 & 2)
    either swap (big chance) or move (small chance)
    */
}

function swap(&$debates, $one, $two) {
    $buffer = $debates[$one[0]]['adjudicators'][$one[1]];
    $debates[$one[0]]['adjudicators'][$one[1]] = $debates[$two[0]]['adjudicators'][$two[1]];
    $debates[$two[0]]['adjudicators'][$two[1]] = $buffer;
}

function actual_sa(&$debates) {
    $temp = 1.0;
    $best_energy = debates_energy($debates);
    $best_debates = $debates;
    $i = 0;
    while ($i < 10000) {
        do {
            $one = random_select($debates);
            $two = random_select($debates);
        } while ($one == $two);
        $before = debate_energy($debates[$one[0]]) + debate_energy($debates[$two[0]]);
        swap($debates, $one, $two);
        $after = debate_energy($debates[$one[0]]) + debate_energy($debates[$two[0]]);
        $diff = $after - $before;
        if (!throw_dice(probability($diff, $temp))) {
            swap($debates, $one, $two); //swap back
        }
        if ($diff < 0) { //better than prev
            $energy = debates_energy($debates);
            if ($energy < $best_energy) {
                $best_debates = $debates;
                $best_energy = $energy;
            }
        }
        $temp = decrease_temp($temp);
        $i++;
    }
    $debates = $best_debates;
}

function probability($diff, $temp) {
    if ($diff < 0)
        return 1;
    if ($diff >= 0)
        return 0.5 * $temp;
}

function throw_dice($probability) {
    $nr = 10000;
    $dice = mt_rand(0, $nr - 1);
    return ($dice <= $probability * $nr);
}

function decrease_temp($temp) {
    $alpha = 0.0001;
    return $temp * (1 - $alpha);
}

?>