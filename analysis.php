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

$title = "Analysis of Algorithms";
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
    <li><a href="contributors.php" class="activemain">Contributors</a></li>
    <li><a href="run/">Run<? if (!$local) echo " Online Demo"; ?></a></li>
    </ul>
</div>


<h2 id="simulation_and_analysis_of_draw_algorithms">Simulation and Analysis of Draw Algorithms</h2>

<p>In an effort to validate draw algorithms for use at the Bangkok worlds, I have developed a framework to simulate a debating tournament. What follows is a discussion of the methodology, results obtained and finally a note on how to run the simulation on your own computer and extending the framework to include more algorithms and models.</p>

<h3 id="methodology">Methodology</h3>

<p>To simulate a tournament, we need a model for generating results for debates.</p>

<p>Currently there are two simulation models that can be used: </p>

<ul>
<li><p><strong>Deterministic Outcome model</strong> : Teams are given an arbitrary ranking 
from 1 to &#8216;n&#8217;, where &#8216;n&#8217; is the number of teams. Whenever teams meet in a 
debate, their positions are determined by the arbitrary ranking assigned to 
them. The highest ranked teams comes first and the lowest ranked teams comes 
last </p></li>
<li><p><strong>Random probability outcome model</strong> : Teams meet each other in a debate 
and the outcome is fully random. Each team has an equal probability of 
coming first, second, third or fourth</p></li>
</ul>

<p>One more model that will be implemented soon is:</p>

<ul>
<li><strong>Weighted probability model</strong> : Teams will be assigned an arbitrary raking as in the Deterministic model above. The outcome of the debate will be a weighted probability value, which means that a team with a higher ranking has a higher chance of winning. Think of it like a loaded dice :)</li>
</ul>

<h3 id="results">Results</h3>

<p>Using the models above, I was able to analyse the <a href="http://tabbie.sourceforge.net/draw_algorithms.php">SilverLine</a> algorithm that was developed by Klass Van Schelven. The following is a link to some of the results</p>

<ul>
<li><a href="analysis/wudc_tab_simulation/sample_output/deterministic_9rounds/index.html">Simulating 9 rounds with Deterministic Outcome (40 teams)</a></li>
<li><a href="analysis/wudc_tab_simulation/sample_output/random_probability_9rounds/index.html">Simulating 9 rounds with Random Probability Outcome (40 teams)</a></li>
<li><a href="analysis/wudc_tab_simulation/sample_output/deterministic_5rounds/index.html">Simulating 5 rounds with Deterministic Outcome (40 teams)</a></li>
<li><a href="analysis/wudc_tab_simulation/sample_output/random_probability_5rounds/index.html">Simulating 5 rounds with Random Probability Outcome (40 teams)</a></li>
</ul>

<p>Apart from the results, the analysis for each round has a table listing the position allocation counts after the draw for that round was calculated. The position allocation contains a column called <em>Debate Badness</em>, which is an indicative score for the quality of position allocation so far. This is based on the <a href="http://tabbie.svn.sourceforge.net/viewvc/tabbie/trunk/run/includes/draw_badness.php?view=markup" title="Draw Badness">Draw Badness Table</a>.</p>

<h3 id="using_the_simulation_framework">Using the Simulation Framework</h3>

<p>The Simulation framework is developed in the <a href="http://www.ruby-lang.org/" title="Ruby Programming Language">Ruby</a> Programming Language. It can be checked out from the <a href="http://tabbie.svn.sourceforge.net/viewvc/tabbie/trunk/analysis">Tabbie SVN Repository</a>.</p>

<p>Before the simulation can be run, the parameters need to be configured. These parameters can be configured in the file <code>config/draw_config.yml</code>. The following parameters can be configured:</p>

<ul>
<li><p><code>num_teams</code> : The number of teams to be used for simulating a tournament. This MUST be a multiple of 4. Also remember that the simulation will take longer if the number of teams is high.</p></li>
<li><p><code>num_rounds</code> : The number of rounds the simulation should run</p></li>
<li><p><code>simulation_algorithm</code> : This can be currently one of <em>deterministic_outcome</em> or <em>random_probability_outcome</em> depending on which of the models above you want to use. You can also create your own modelling scenarios and add them (See next section).</p></li>
<li><p><code>draw_algorithm</code> : Current this can only have the value <em>silver_line</em>, but you can create your own draw algorithms and add them (See next section). </p></li>
</ul>

<p>Once you have configured the parameters, you have to just run the ruby program file <code>draw_simulation.rb</code>. On a Linux command line, it would look like : </p>

<pre><code>deepak@feistyvm:~/tabbie/analysis/wudc_tab_simulation$ ruby draw_simulation.rb 
Calculating Draw for Round 1
Simulating Results for Round 1
Calculating Draw for Round 2
Simulating Results for Round 2
Calculating Draw for Round 3
Simulating Results for Round 3
Calculating Draw for Round 4
Simulating Results for Round 4
Calculating Draw for Round 5
Simulating Results for Round 5
</code></pre>

<p>After this the results of the simulation are created in a sub-folder inside the <code>output</code> folder. The folder is named according to the current date and time, for example <em>23_Sep_2007-142031</em>. Open the file <code>index.html</code> file to view the results.</p>

<h3 id="extending_improving_the_simulation_framework">Extending/Improving the Simulation Framework</h3>

<p>Here is an explanation of the layout of main components the source tree:</p>

<ul>
<li><p><code>config/draw_config.yml</code> : Configuration file for</p></li>
<li><p><code>draw_simulation.rb</code> : Entry point into the simulation program</p></li>
<li><p><code>templates</code> : This folder contains <a href="http://www.ruby-doc.org/stdlib/libdoc/erb/rdoc/classes/ERB.html" title="erb: Ruby Standard Library Documentation">Ruby ERB templates</a> for the HTML code used in the analysis. This template is then rendered into actual HTML for displaying the results of the simulation.</p></li>
<li><p><code>lib/algorithms</code> : Contains the draw algorithms. </p></li>
<li><p><code>lib/simulation</code> : Contains the simulation models.</p></li>
</ul>

<h4 id="developing_your_own_algorithm">Developing your own algorithm</h4>

<p>Here is some sample code that you can use as a start point for implementing your own draw algorithm. You will have to write a Ruby version of the algorithm to simulate it.</p>

<pre><code>module MyAlgorithm
    def self.do_draw(teams_hash)
        # TODO implement algorithm here
    end
end
</code></pre>

<p>You can implement the <code>do_draw</code> method (look at <code>silver_line.rb</code> for a sample implementation) and then save file as <code>lib/algorithms/my_algorithm.rb</code>.</p>

<h4 id="developing_your_own_simulation_model">Developing your own simulation model</h4>

<p>Here is some sample code that you can use as a starting point for implementing your own simulation algorithm.</p>

<pre><code>module MyCustomOutcome    
    def self.calculate_results(debates, teams_hash)
        # TODO Implement simulation model here
    end
end
</code></pre>

<p>You can implement the <code>calculate_results</code> method (look at <code>random_probability_outcome.rb</code> for a sample implementation) and then save file as <code>lib/simulation/my_custom_outcome.rb</code>.    </p>

<h3 id="feedback_and_queries">Feedback and Queries</h3>

<p>Mail me at deepak.jois@gmail.com, or join the <a href="http://groups.google.com/group/tabbie-devel/" title="Tabbie Development | Google Groups">Tabbie Development Google Group</a>.</p>

<?php require("run/view/footer.php"); ?>
