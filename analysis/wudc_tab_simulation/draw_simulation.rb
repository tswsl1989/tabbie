#!/usr/bin/env ruby

# Utility classes
require 'yaml'
               
# Extensions to Ruby core classes
require 'lib/ext/extensions'

# Data Classes
require 'lib/data/team'

# Simulation Classes
require 'lib/simulation/deterministic_outcome'
require 'lib/simulation/random_probability_outcome'
require 'lib/simulation/weighted_probability_outcome'

# Draw Algorithm Classes
require 'lib/algorithms/silver_line.rb'

# Display classes
require 'lib/output/renderer'

# Statistics
require 'lib/algorithms/statistics.rb'


# Read config
config = YAML::load(File.read("#{File.dirname(__FILE__)}/config/draw_config.yml"))

inter_round_array = Array.new(config["num_rounds"]+1,0.0)
theoretical_array = Array.new(config["num_rounds"]+1,0.0)
breakingstb_array = Array.new(config["num_rounds"]+1,0.0)

distribution=DebateDistribution.new(config["num_rounds"])

(1..config['iterations']).to_a.each do |iteration|
  # Generate teams
  teams_hash = {}
  config["num_teams"].times { |n| 
      teams_hash["Team #{n}"] = Team.new("Team #{n}", n)
  }        

  #renderer = Renderer.new(File.dirname(__FILE__))
  #renderer.render_index_template(config['num_rounds'], config["draw_algorithm"], config["simulation_algorithm"])

  #This is a kludge: the correlation of actual ranks with theoretical ranks is insane because there are at most 27 actual ranks and 200 theoretical ones!
  theoretical_hash = {}
  teams_hash.sort { |a,b| a[1].rank <=> b[1].rank }.inject([distribution.teamsonpoints(config["num_teams"],config["num_rounds"],config["num_rounds"]*3),config["num_rounds"]*3,0,0]) do |passback, team|
    #puts passback[0].to_s+" "+passback[1].to_s+" "+passback[2].to_s
    #passback[0] is the number of teams still to be ranked on this point bracket; passback[1] is the point bracket
    #passback[2] is the current rank; passback[3] is the number of teams so far ranked
    while passback[0]==0
     # puts "No more teams to be ranked on point bracket #{passback[1]}"
      passback[1] = passback[1] - 1
      passback[0] = distribution.teamsonpoints(config["num_teams"],config["num_rounds"],passback[1])
      #puts "New point bracket has #{passback[0]} teams to be ranked"
      if passback[0]>0
        passback[2]=passback[3]+1
        #puts "Dropped rank to #{passback[2]}"
      end
      #Rank all remaining teams on 0 - catch rounding errors
      if (passback[1]<=0)
        passback[0]=100000
      end
    end
    if (passback[2]==0)
      passback[2]==1
    end
    theoretical_hash[team[0]]=passback[2]
    passback[0] = passback[0]-1
    passback[3] = passback[3]+1
    passback
  end

  this_inter_round_array=Array.new(config["num_rounds"]+1,0.0)
  this_theoretical_array=Array.new(config["num_rounds"]+1,0.0)
  this_breakingstb_array=Array.new(config["num_rounds"]+1,0.0)
  thisroundhash = {}
  teams_hash.each { |team| thisroundhash[team[0]]=1}
  (1..config['num_rounds']).to_a.each do |round|
    #puts "Calculating Draw for Round #{round}"
    # Calculate Draw
    debates = config['draw_algorithm'].camelize.constantize::do_draw(teams_hash)

    # Update positions
    debates.each do |debate|
      debate.each_with_index do |team_code, idx|
        pos = %w(og oo cg co)[idx]
        pos_count = teams_hash[team_code].send(pos)
        pos_count += 1
        teams_hash[team_code].send("#{pos}=",pos_count)
      end        
    end

    #puts "Simulating Results for Round #{round}"
    # Simulate Results
    results = config['simulation_algorithm'].camelize.constantize::calculate_results(debates, teams_hash, config["randomness_weight"])
    corr_hash = {}
    teams_hash.sort { |a,b| a[1].points <=> b[1].points }.reverse.inject([1,0,0]) do |rankpoints, element|
      #rankpoints[0]=current rank; rankpoints[1]=points; rankpoints[2]=teams so far participated
      #puts "Element under consideration #{element[0]} with points #{element[1].points}. Current rank is #{rankpoints[0]} for points #{rankpoints[1]}"
      if (rankpoints[0] == 1) && (rankpoints[1] == 0) #Detect top points going down...
        rankpoints[1] = element[1].points
        #puts "Top points detected as #{rankpoints[1]}"
      end
      if element[1].points < rankpoints[1]
        rankpoints[1] = element[1].points
        rankpoints[0] = rankpoints[2] + 1
        #puts "New rank #{rankpoints[0]} due to lower points #{rankpoints[1]}"
      end
      #puts element[1].points
      corr_hash[element[1].team_code] = rankpoints[0]
      rankpoints[2] += 1
      rankpoints
    end  
    lastroundhash = thisroundhash
    thisroundhash = corr_hash
    this_inter_round_array[round]=correlation(thisroundhash.sort.collect{|item| item[1]},lastroundhash.sort.collect{|item| item[1]})
    this_theoretical_array[round]=correlation(thisroundhash.sort.collect{|item| item[1]},theoretical_hash.sort.collect{|item| item[1]})
    this_breakingstb_array[round]=thisroundhash.sort{ |a,b| a[1] <=> b[1] }.slice(0..config["teams_breaking"]).select{|element| lastroundhash[element[0]]>config["teams_breaking"]}.size/config["teams_breaking"]
    
    #renderer.render_round_template(round, debates, results, teams_hash)
  end
  inter_round_array=(0..inter_round_array.size-1).to_a.map!{|round| (((iteration-1)*inter_round_array[round]+this_inter_round_array[round])/iteration)}
  theoretical_array=(0..theoretical_array.size-1).to_a.map!{|round| (((iteration-1)*theoretical_array[round]+this_theoretical_array[round])/iteration)}
  breakingstb_array=(0..breakingstb_array.size-1).to_a.map!{|round| (((iteration-1)*breakingstb_array[round]+this_breakingstb_array[round])/iteration)}
  puts "Iteration #{iteration}"
  puts("     1     2     3     4     5     6     7     8     9")
  puts inter_round_array[1..9].inject(""){|string, element| string += " "+element.to_s[0..4].rjust(5)}
  puts theoretical_array[1..9].inject(""){|string, element| string += " "+element.to_s[0..4].rjust(5)}
  puts breakingstb_array[1..9].inject(""){|string, element| string += " "+element.to_s[0..4].rjust(5)}
end
puts "========================================================"
puts "RESULTS"
puts "Teams:      #{config['num_teams']}"
puts "Rounds:     #{config['num_rounds']}"
puts "Weight:     #{config['randomness_weight']}"
puts "Algorithm:  #{config['simulation_algorithm']}"
puts "Iterations: #{config['iterations']}"
puts "--------------------------------------------------------"
puts("     1     2     3     4     5     6     7     8     9")
puts inter_round_array[1..9].inject(""){|string, element| string += " "+element.to_s[0..4].rjust(5)}+ " Inter-Round Correlation"
puts theoretical_array[1..9].inject(""){|string, element| string += " "+element.to_s[0..4].rjust(5)}+ " Theoretical Correlation"
puts breakingstb_array[1..9].inject(""){|string, element| string += " "+element.to_s[0..4].rjust(5)}+ " Break Stability Fraction"
puts "========================================================"
