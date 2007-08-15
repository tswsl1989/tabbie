<?
//for information on what simulated annealing is, see: http://en.wikipedia.org/wiki/Simulated_annealing

/*
debate data structure:
desired average
desired amount of adjudicators
universities in the debate
teams in the debate

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

function allocate_simulated_annealing() {
    //should work without params...
    srand(0);
    get_info_from_db();
    set_desired_averages();
    actual simulated annealing
    write_to_db();
}

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

function random_select(...) {
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
        random_select()
        //calculate debate energies pre and post, calc diff.
        calculate likeliness of move($diff, $temp);
        make move with that likeliness
        $temp = decrease_temp($temp);
        if ($diff < 0) { //better than prev
            $energy = debates_energy()
            if ($energy < $best_energy) {
                save state
                $best_energy = $energy;
            }
        }
    }
}

function probability($diff, $temp) {
    if ($diff < 0)
        return 1;
    if ($diff > 1)
        return 0.5 * $temp; //obvious point for optimalisations
}

function decrease_temp($temp) {
    $alpha = 0.01;
    return $temp * (1 - $alpha);
}

?>