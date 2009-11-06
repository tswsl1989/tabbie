module WeightedProbabilityOutcome
    def self.calculate_results(debates, teams_hash, randomness_weight)
        results = []
        debates.each do |debate|
            puts "New Debate"
            result_order = debate.sort { |x,y| teams_hash[y].rank*(rand(2*randomness_weight)-randomness_weight)/1000.0 <=> teams_hash[x].rank*(rand(2*randomness_weight)-randomness_weight)/1000.0 }
            # Add points
            result_order.each_with_index do |team_code, idx|
                teams_hash[team_code].points += idx
                puts "#{idx} #{team_code} #{teams_hash[team_code].rank}"
            end
            results << result_order
        end
        results
    end
end