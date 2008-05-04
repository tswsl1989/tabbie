require "#{File.dirname(__FILE__)}/debate_badness"
module SilverLine
    def self.do_draw(teams_hash)
        @@teams_hash = teams_hash
        team_codes = teams_hash.keys
        
         # Shuffle
         team_codes = team_codes.sort_by { rand }
         
         # Sort in reverse order
         team_codes = team_codes.sort { |x,y| teams_hash[y].points <=> teams_hash[x].points 
          }
         
         @@debates = team_codes.in_groups_of 4
         
         prev_soln = 0
         while teams_badness > 0
             total_badness =  teams_badness
              if prev_soln == total_badness
                  break  
              end
              prev_soln = total_badness
              @@debates.each_with_index do |debate, x|
                  debate.each_with_index do |team_code, pos|
                      if team_badness(team_code,pos) > 0
                          #puts "find_best_swap_for(#{team_code}, #{pos})"
                          if find_best_swap_for(team_code,pos)
                              break
                          end
                      end
                  end
              end              
         end         
         return @@debates
    end
        
    def self.teams_badness
        result = 0
        @@debates.each_with_index do |debate, x|
            debate.each_with_index do |team_code, pos|
                result += team_badness(team_code, pos)
            end
        end
        result
    end
    
    def self.team_badness(team_code, position_index)
        pos = [@@teams_hash[team_code].og,
                     @@teams_hash[team_code].oo,
                     @@teams_hash[team_code].cg,
                     @@teams_hash[team_code].co]
       pos[position_index] += 1
       DebateBadness.lookup(*pos) # Splat!
    end
    
    def self.find_best_swap_for(team_a_code, team_a_pos)
        best_effect = 0
        best_team_b = false
        @@debates.each_with_index do |debate, debate_idx|
            debate.each_with_index do |team_b_code, team_b_pos|
                if is_swappable(team_a_code, team_b_code)
                    #puts "team_badness(#{team_a_code}, #{team_a_pos}) + team_badness(#{team_b_code}, #{team_b_pos})"
                    current = team_badness(team_a_code, team_a_pos) + team_badness(team_b_code, team_b_pos)
                    future = team_badness(team_a_code, team_b_pos) + team_badness(team_b_code, team_a_pos)
                    net_effect = future - current
                    if net_effect < best_effect
                        best_effect = net_effect
                        best_team_b = team_b_code
                    end
                end
            end
        end
        
        if best_team_b
            swap_teams(team_a_code, best_team_b)
            return true
        else
            return false
        end
    end
    
    def self.swap_teams(team_a_code, team_b_code)
        team_a_loc = find_location_of(team_a_code)
        team_b_loc = find_location_of(team_b_code)
        @@debates[team_b_loc[0]][team_b_loc[1]] = team_a_code
        @@debates[team_a_loc[0]][team_a_loc[1]] = team_b_code
    end
    
    def self.find_location_of(team_search_code)
        @@debates.each_with_index do |debate, debate_idx|
            debate.each_with_index do |team_code, team_idx|
                return [debate_idx, team_idx] if team_code == team_search_code
            end
        end           
    end
    
    def self.is_swappable(team_a, team_b)
       (team_a != team_b) && ((@@teams_hash[team_a].points == @@teams_hash[team_b].points) || (are_in_same_bracket(team_a,team_b)))
    end
    
    def self.are_in_same_bracket(team_a, team_b)
        team_a_level = -1
        team_b_level = -2
        @@debates.each_with_index do |debate, debate_idx|
            level = debate.collect { |t| @@teams_hash[t].points}.max            
            debate.each_with_index do |team_code, pos|
                if team_code == team_a
                    team_a_level = level
                    #puts "team_a_level - #{team_a_level}"
                elsif team_code == team_b
                    team_b_level = level
                    #puts "team_a_level - #{team_b_level}"                    
                end
            end
        end
        team_a_level == team_b_level
    end
end
