# Returns an array, containing a subarray of team codes for each bracket
 def self.divide_into_brackets(teams_hash)
     # Divide into brackets
     teams_grouped_by_points = team_codes.group_by { |team_code| teams_hash[team_code].points }
     points_arr = teams_grouped_by_points.keys.sort { |x,y| y <=> x } # Descending order of points
     # Pull up if needed
     points_arr.each_with_index do |p,i|
         if (teams_grouped_by_points[p].size % 4) > 0
             next_group = i+1
             while (teams_grouped_by_points[p].size % 4 == 0)
                 next_group_points = points_arr[next_group]
                 pull_up_team = teams_grouped_by_points[next_bracket_points].pop
                 if !pull_up_team
                     next_group+= 1
                 else
                     teams_grouped_by_points[p].push pull_up_team
                 end
             end
         end
     end
     brackets = teams_grouped_by_points.keys.select{ |p| teams_grouped_by_points[p].size > 0 }.collect{ |p| teams_grouped_by_points[p] }        
 end