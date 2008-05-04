## Simulation and Analysis of Draw Algorithms

In an effort to validate draw algorithms for use at the Bangkok worlds, I have developed a framework to simulate a debating tournament. What follows is a discussion of the methodology, results obtained and finally a note on how to run the simulation on your own computer and extending the framework to include more algorithms and models.

### Methodology
To simulate a tournament, we need a model for generating results for debates.
 
Currently there are two simulation models that can be used: 

* **Deterministic Outcome model** : Teams are given an arbitrary ranking 
from 1 to 'n', where 'n' is the number of teams. Whenever teams meet in a 
debate, their positions are determined by the arbitrary ranking assigned to 
them. The highest ranked teams comes first and the lowest ranked teams comes 
last 

* **Random probability outcome model** : Teams meet each other in a debate 
and the outcome is fully random. Each team has an equal probability of 
coming first, second, third or fourth

One more model that will be implemented soon is:

* **Weighted probability model** : Teams will be assigned an arbitrary raking as in the Deterministic model above. The outcome of the debate will be a weighted probability value, which means that a team with a higher ranking has a higher chance of winning. Think of it like a loaded dice :)

### Results
Using the models above, I was able to analyse the [SilverLine](http://tabbie.sourceforge.net/draw_algorithms.php) algorithm that was developed by Klass Van Schelven. The following is a link to some of the results

* [Simulating 9 rounds with Deterministic Outcome (40 teams)](analysis/wudc_tab_simulation/sample_output/deterministic_9rounds/index.html)
* [Simulating 9 rounds with Random Probability Outcome (40 teams)](analysis/wudc_tab_simulation/sample_output/random_probability_9rounds/index.html)
* [Simulating 5 rounds with Deterministic Outcome (40 teams)](analysis/wudc_tab_simulation/sample_output/deterministic_5rounds/index.html)
* [Simulating 5 rounds with Random Probability Outcome (40 teams)](analysis/wudc_tab_simulation/sample_output/random_probability_5rounds/index.html)

Apart from the results, the analysis for each round has a table listing the position allocation counts after the draw for that round was calculated. The position allocation contains a column called *Debate Badness*, which is an indicative score for the quality of position allocation so far. This is based on the [Draw Badness Table](http://tabbie.svn.sourceforge.net/viewvc/tabbie/trunk/run/includes/draw_badness.php?view=markup "Draw Badness").

### Using the Simulation Framework
The Simulation framework is developed in the [Ruby](http://www.ruby-lang.org/ "Ruby Programming Language") Programming Language. It can be checked out from the [Tabbie SVN Repository](http://tabbie.svn.sourceforge.net/viewvc/tabbie/trunk/analysis).

Before the simulation can be run, the parameters need to be configured. These parameters can be configured in the file `config/draw_config.yml`. The following parameters can be configured:

* `num_teams` : The number of teams to be used for simulating a tournament. This MUST be a multiple of 4. Also remember that the simulation will take longer if the number of teams is high.

* `num_rounds` : The number of rounds the simulation should run

* `simulation_algorithm` : This can be currently one of *deterministic_outcome* or *random_probability_outcome* depending on which of the models above you want to use. You can also create your own modelling scenarios and add them (See next section).

* `draw_algorithm` : Current this can only have the value *silver_line*, but you can create your own draw algorithms and add them (See next section). 

Once you have configured the parameters, you have to just run the ruby program file `draw_simulation.rb`. On a Linux command line, it would look like : 

    deepak@feistyvm:~/tabbie/analysis/wudc_tab_simulation$ ruby draw_simulation.rb 
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

After this the results of the simulation are created in a sub-folder inside the `output` folder. The folder is named according to the current date and time, for example *23_Sep_2007-142031*. Open the file `index.html` file to view the results.

### Extending/Improving the Simulation Framework
Here is an explanation of the layout of main components the source tree:

* `config/draw_config.yml` : Configuration file for

* `draw_simulation.rb` : Entry point into the simulation program

* `templates` : This folder contains [Ruby ERB templates](http://www.ruby-doc.org/stdlib/libdoc/erb/rdoc/classes/ERB.html "erb: Ruby Standard Library Documentation") for the HTML code used in the analysis. This template is then rendered into actual HTML for displaying the results of the simulation.

* `lib/algorithms` : Contains the draw algorithms. 

* `lib/simulation` : Contains the simulation models.

#### Developing your own algorithm
Here is some sample code that you can use as a start point for implementing your own draw algorithm. You will have to write a Ruby version of the algorithm to simulate it.

    module MyAlgorithm
        def self.do_draw(teams_hash)
            # TODO implement algorithm here
        end
    end
    
You can implement the `do_draw` method (look at `silver_line.rb` for a sample implementation) and then save file as `lib/algorithms/my_algorithm.rb`.

#### Developing your own simulation model
Here is some sample code that you can use as a starting point for implementing your own simulation algorithm.

    module MyCustomOutcome    
        def self.calculate_results(debates, teams_hash)
            # TODO Implement simulation model here
        end
    end
    
You can implement the `calculate_results` method (look at `random_probability_outcome.rb` for a sample implementation) and then save file as `lib/simulation/my_custom_outcome.rb`.    

### Feedback and Queries
Mail me at deepak.jois@gmail.com, or join the [Tabbie Development Google Group](http://groups.google.com/group/tabbie-devel/ "Tabbie Development | Google Groups").