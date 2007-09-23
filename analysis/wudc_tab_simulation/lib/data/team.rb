class Team
    attr_accessor :og, :oo, :cg, :co, :team_code, :points, :rank
    
    def initialize(team_code, rank)
        self.team_code = team_code
        self.rank = rank
        self.points = self.og = self.oo = self.cg = self.co = 0
    end
end