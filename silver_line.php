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
 * end license */ ?>
A more elaborate description of the Silver Line Algorithm.

[[Definitions - where does this go?]]
Points: The total amount of points accumilated by a team so far - whereby winning a round is accredited with 3 points, becoming second with 2, third 1 and losing 0 points.

Position: Opening Government ... Closing Opposition. In the context of this article position never referres to winning or losing a debate. (In fact, the names of these positions are not quite relevant and may be replaced by numbers in any implementation or discussion.)

<h3>Problem Description</h3>
<p>
In the context of Debating Tabbing Software there are two main challenges to be solved for each draw:
<ol>
<li>The allocation of teams to debates</li>
<li>The allocation of adjudicators to debates</li>
</ol>
The Silver Line algorithm is an algorithm that provides a solution for the first of these problems.
</p><p>
The Silver Line algorithm is allocates teams to debates according to a specific set of rules: the <a href="doc/Worlds_Tab_Rules_-_DRAFT.doc">WUDC rules sections 3a - 3h</a> (referred to in the following as "the rules". For the purpose of this article these rules will be reformulated in an equivalent way here. The rules have two parts:
<ol>
<li>Powerpairing</li>
<li>Fair distribution of positions over teams.</li>
</ol>
The rules for powerpairing take precedence over rules for fair distribution. According to the rules, any solution must comply with the section on powerpairing - and then may try to come up with a solution that is as fair as possible in terms of distribution of the positions over teams.
</p><p>
The rules on powerpairing 


It is very easy to find a solution.

Given list of teams, ordered by the amount of 

<p>
</p>

[[A short introduction into optimization of non-linear problems.]]

<h3>Scoring</h3>
<p>
In the context of this article we will refer to "Score" as a measure of the effectiveness of an algorithm in achieving 

A lower score is to be considered more optimal, with 0 as a (not nececerally achievable) optimal score.
</p>
<p>


To define a 'good' allocation of teams to positions, and to be able to compare different correct solutions to the draw, we need some form of scoring the solutions. The scoring algorithm used has the following properties:
<ol>
<li>The more unequal the distribution of positions, the higher the score.</li>
<li>The differences between increasingly unequal distributions increase, i.e. it is more worthwhile to fix a grave problem than a small one.</li>
</ol>
I have found a function that satisfies these two properties and imported it as a map into PHP. The various scores can be inspected in <a href="http://tabbie.svn.sourceforge.net/viewvc/tabbie/trunk/run/includes/draw_badness.php?view=markup">the code</a>
</p><p>

[[note that this mechanism for scoring and correctness can be used to evaluate any algorithm]]

Search Space:
Possible Swaps


Local Optimums are good.


Finding a global optimum


Simulated usage: Findings (by Deepak)

Other considerations, further work.

Note that the distinction between round 1 and other rounds that is made in the rules is superfluous.
