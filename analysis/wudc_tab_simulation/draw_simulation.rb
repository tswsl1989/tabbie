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

# Draw Algorithm Classes
require 'lib/algorithms/silver_line.rb'

# Display classes
require 'lib/output/renderer'


# Read config
config = YAML::load(File.read("#{File.dirname(__FILE__)}/config/draw_config.yml"))

# Generate teams
teams_hash = {}
config["num_teams"].times { |n| 
    teams_hash["Team #{n}"] = Team.new("Team #{n}", n)
}        

renderer = Renderer.new(File.dirname(__FILE__))
renderer.render_index_template(config['num_rounds'], config["draw_algorithm"], config["simulation_algorithm"])
(1..config['num_rounds']).to_a.each do |round|
    puts "Calculating Draw for Round #{round}"
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
    
    puts "Simulating Results for Round #{round}"
    # Simulate Results
    results = config['simulation_algorithm'].camelize.constantize::calculate_results(debates, teams_hash)    
    renderer.render_round_template(round, debates, results, teams_hash)
end
