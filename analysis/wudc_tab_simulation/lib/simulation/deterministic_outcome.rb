module DeterministicOutcome    
    def self.calculate_results(debates, teams_hash)
        results = []
        debates.each do |debate|
            result_order = debate.sort { |x,y| teams_hash[y].rank <=> teams_hash[x].rank }
            # Add points
            result_order.each_with_index do |team_code, idx|
                teams_hash[team_code].points += idx
            end
            results << result_order
        end
        results
    end
end