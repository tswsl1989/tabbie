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

$title = "Tabbie - BP Debating Tab Software - Draw Algorithms";
$dir_hack = "run/";
require("run/view/header.php");
$local = ($_SERVER["SERVER_NAME"] != "tabbie.sourceforge.net");
?>

<div id="mainmenu">
    <h2 class="hide">Main Menu</h2>
    <ul>
    <li><a href="index.php">Home</a></li>
    <li><a href="http://sourceforge.net/project/showfiles.php?group_id=199347">Download</a></li>
    <li><a href="installation.php">Installation Guide</a></li>
    <li><a href="contributors.php">Contributors</a></li>
    <li><a href="run/">Run<? if (!$local) echo " Online Demo"; ?></a></li>
    </ul>
</div>

<h3>Draw Algorithms for the WUDC Tab rules</h3>
<p>
This 'article' provides an overview of my thoughts so far on providing an implementation for the <a href="doc/Worlds_Tab_Rules_-_DRAFT.doc">WUDC Draw Rules</a>. It is not meant as a full description of either those thoughts, or the resulting algorithm, but rather as (1) enough evidence to support the claim that Tabbie's algorithm is currently the world's best and (2) enough stepping stones for further development of better algorithms. The expected level of the reader is high - i.e. Software Developer or Algorithm Developer.
</p>

<h3>Correctness</h3>
<p>
The definition of correctness is given in the <a href="doc/Worlds_Tab_Rules_-_DRAFT.doc">WUDC rules sections 3a - 3g</a> (3h is simply a reiteration) and will not be repeated here.
</p><p>
An automatic check for correctness is part of Tabbie. This check follows the definition given here and can be inspected at <a href="http://tabbie.svn.sourceforge.net/viewvc/tabbie/trunk/run/includes/draw.php?view=markup">validate_debates_in_brackets and create_brackets</a>.
<h4>Trivial Correct Implementation</h4>
</p><p>
In fact, any algorithm that is correct under this definition and does not take into account anything other than the amount of points a team has scored so far and the positions this team has taken can be used at a WUDC-rules tournament. 
</p><p>
Such an algorithm can easily be found, for example by taking all teams, ordering them by number of points so far, and them placing them into debates without paying any regard to the position they are taking. This algorithm is included in Tabbie (but not used) and can be inspected in the <a href="http://tabbie.svn.sourceforge.net/viewvc/tabbie/trunk/run/draw/algorithms/minimal_algorithm.php?view=markup">Subversion Repository</a>.
</p>
<h3>Scoring</h3>
<p>
To define a 'good' allocation of teams to positions, and to be able to compare different correct solutions to the draw, we need some form of scoring the solutions. The scoring algorithm used has the following properties:
<ol>
<li>The more unequal the distribution of positions, the higher the score.</li>
<li>The differences between increasingly unequal distributions increase, i.e. it is more worthwhile to fix a grave problem than a small one.</li>
</ol>
I have found a function that satisfies these two properties and imported it as a map into PHP. The various scores can be inspected in <a href="http://tabbie.svn.sourceforge.net/viewvc/tabbie/trunk/run/includes/draw_badness.php?view=markup">the code</a>
</p><p>
Calculation of a score using the above calculation, are run by Tabbie after the draw has been calculated. The user is notified of the score and warned if any algorithm does not provide a correct solution. The score calculation can be verified at <a href="http://tabbie.svn.sourceforge.net/viewvc/tabbie/trunk/run/includes/draw.php?view=markup">debates_badness</a>.
</p>

<h3>Further Demands to a good Algorithm</h3>
<p>
Furthermore, any algorithm that makes its way into Tabbie as a WUDC algorithm should have the following property: though it's results must be random, they must yield the same results for every created draw. (This can be achieved by using semi-randomness and a fixed seed) This is important for two reasons. Firstly, this makes sure that no one can temper with the draw by trying another draw for the same round to benefit certain teams or for other reasons. Secondly, only if the algorithm yields the same result every time can it be meaningfully compared to other algorithms.
</p>

<h3>General Observations</h3>
<p>
An algorithm that considers all possible distributions of teams over positions may easily take exponential time. This is because, as long as pools are connected by pull ups, any change at any place may influence all other teams.
</p><p>
If brackets / pools have a clean break (i.e. there is no pull up), the two halves above and below this break may be calculated seperately. This can drastically decrease execution times.
</p><p>
Some things can be ignored. It doesn't matter for a (algorithm's) score in which debate on a particular level/bracket a team is located. The problem can therefor be reduced to a number of openings for "Opening Government", etc. on a certain bracket.
</p><p>
It's not interesting to consider the position's names ("Opening Government", etc.). These can simply be referred to as "0", "1", "2" and "3".
</p>
<h3>The Silver Line Algorithm</h3>
<p>
The Silver Line Algorithm is the best known implementation worldwide of an algorithm that computes the draw within reasonable time. In fact, the only other implementations I know are the Cragie Tab (which does not take all options into account as will be demonstrated below) and the Tournaman system (which has closed source code). I'm open for debate on this claim, but will leave it here as long as it goes uncontested.
</p><p>
The most recent version of the algorithm's source code can be always be found in the <a href="http://tabbie.svn.sourceforge.net/viewvc/tabbie/trunk/run/draw/algorithms/silver_line.php?view=markup">subversion repository</a>, or simply by downloading Tabbie and opening the relevant files.
</p>

</p>
<h4>Implementation</h4>
<p>
The Algorithm starts with the above trivial implementation that provides a correct, but not optimal solution. After this, Silver Line "just keeps swapping" teams that can be swapped. Teams that can be swapped have either:
<ul>
<li>The same number of points</li>
<li>Are in a debate of the same level (bracket/pool)</li>
</ul>
If such a swap improves the overall score it will be executed. If there are no more swaps that directly improve the score, the algorithm terminates. Siler Line never terminates if there is a better solution available by swapping just two teams. The proof of this is left as an exercise to the reader.
</p>

<h4>Limitations</h4>
<p>
Silver Line can (though not easily and not often) get stuck in a local optimum. This occurs when no swap of two teams actually directly benefits the score, and the algorithm terminates. However, it is thinkable that swapping one team with another would allow for another swap that does increase the score. (This of course applies recursively).

</p><p>
Copyright Klaas van Schelven, GPL 2.0.
</p>

<?php require("run/view/footer.php"); ?>
