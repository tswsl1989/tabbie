module DeterministicOutcome    
    def self.calculate_results(debates, teams_hash, dummy)
        results = []
        debates.each do |debate|
            #puts "New debate"
            result_order = debate.sort { |x,y| teams_hash[y].rank <=> teams_hash[x].rank }
            # Add points
            result_order.each_with_index do |team_code, idx|
                teams_hash[team_code].points += idx
                #puts "#{idx} #{team_code} #{teams_hash[team_code].rank}"
            end
            results << result_order
        end
        results
    end
end