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
    //should work without params...
    $nextround = get_num_rounds() + 1;
    srand(0);
    $debates = temp_debates_foobar($nextround);
    $adjudicators = get_active_adjudicators();
    set_desired_averages($debates, get_average_ranking($adjudicators));
    initial_distribution($debates, $adjudicators);
    print_r($debates);
    //actual_simulated_annealing();
    //write_to_db();
}

function initial_distribution(

function debate_energy(&$debate) {
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

function random_select() {
    /*
    take any adjudicator
    find neighbouring debate (and team), giving pref. to closer debates (at least dist 1 & 2)
    either swap (big chance) or move (small chance)
    */
}

function actual_sa() {
    $temp = 1.0;
    $best_energy = infinite;
    while (something) {
        random_select();
        //calculate debate energies pre and post, calc diff.
        if (throw_dice(probability($diff, $temp)))
            make_move();
        $temp = decrease_temp($temp);
        if ($diff < 0) { //better than prev
            $energy = debates_energy();
            if ($energy < $best_energy) {
                save_state();
                $best_energy = $energy;
            }
        }
    }
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
    $alpha = 0.01;
    return $temp * (1 - $alpha);
}

?>