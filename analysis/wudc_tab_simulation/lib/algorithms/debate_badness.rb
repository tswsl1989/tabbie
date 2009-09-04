class DebateBadness
    def self.lookup(og_count, oo_count, cg_count, co_count)
        pos = [og_count, oo_count, cg_count, co_count].sort
        return BADNESS_TABLE[pos.inject(""){|s,i| s+= "#{i}, "}.chomp(", ")] # chomp at the end to strip off the last instance of comma insertion
    end
    
    BADNESS_TABLE = {
        "0, 0, 0, 0" => 0,
        "0, 0, 0, 1" => 0,
        "0, 0, 0, 2" => 4,
        "0, 0, 0, 3" => 36,
        "0, 0, 0, 4" => 144,
        "0, 0, 0, 5" => 324,
        "0, 0, 0, 6" => 676,
        "0, 0, 0, 7" => 1296,
        "0, 0, 0, 8" => 2304,
        "0, 0, 0, 9" => 3600,
        "0, 0, 1, 1" => 0,
        "0, 0, 1, 2" => 4,
        "0, 0, 1, 3" => 36,
        "0, 0, 1, 4" => 100,
        "0, 0, 1, 5" => 256,
        "0, 0, 1, 6" => 576,
        "0, 0, 1, 7" => 1156,
        "0, 0, 1, 8" => 1936,
        "0, 0, 2, 2" => 16,
        "0, 0, 2, 3" => 36,
        "0, 0, 2, 4" => 100,
        "0, 0, 2, 5" => 256,
        "0, 0, 2, 6" => 576,
        "0, 0, 2, 7" => 1024,
        "0, 0, 3, 3" => 64,
        "0, 0, 3, 4" => 144,
        "0, 0, 3, 5" => 324,
        "0, 0, 3, 6" => 576,
        "0, 0, 4, 4" => 256,
        "0, 0, 4, 5" => 400,
        "0, 1, 1, 1" => 0,
        "0, 1, 1, 2" => 4,
        "0, 1, 1, 3" => 16,
        "0, 1, 1, 4" => 64,
        "0, 1, 1, 5" => 196,
        "0, 1, 1, 6" => 484,
        "0, 1, 1, 7" => 900,
        "0, 1, 2, 2" => 4,
        "0, 1, 2, 3" => 16,
        "0, 1, 2, 4" => 64,
        "0, 1, 2, 5" => 196,
        "0, 1, 2, 6" => 400,
        "0, 1, 3, 3" => 36,
        "0, 1, 3, 4" => 100,
        "0, 1, 3, 5" => 196,
        "0, 1, 4, 4" => 144,
        "0, 2, 2, 2" => 4,
        "0, 2, 2, 3" => 16,
        "0, 2, 2, 4" => 64,
        "0, 2, 2, 5" => 144,
        "0, 2, 3, 3" => 36,
        "0, 2, 3, 4" => 64,
        "0, 3, 3, 3" => 36,
        "1, 1, 1, 1" => 0,
        "1, 1, 1, 2" => 0,
        "1, 1, 1, 3" => 4,
        "1, 1, 1, 4" => 36,
        "1, 1, 1, 5" => 144,
        "1, 1, 1, 6" => 324,
        "1, 1, 2, 2" => 0,
        "1, 1, 2, 3" => 4,
        "1, 1, 2, 4" => 36,
        "1, 1, 2, 5" => 100,
        "1, 1, 3, 3" => 16,
        "1, 1, 3, 4" => 36,
        "1, 2, 2, 2" => 0,
        "1, 2, 2, 3" => 4,
        "1, 2, 2, 4" => 16,
        "1, 2, 3, 3" => 4,
        "2, 2, 2, 2" => 0,
        "2, 2, 2, 3" => 0
    }
end

class DebateDistribution
  def initialize(rounds)
    @requests=0.0
    @cachehits=0.0
    @maxrounds=rounds
    @cache=Array.new(rounds+1)
    @cache.map! { Array.new(rounds*3+1) }
    #The following lines set some of the termination conditions for the recursion
    @cache[0][0]=1
    (1..@cache[0].size-1).to_a.each{ |index| @cache[0][index]=0}
    @cache[1][0]=@cache[1][1]=@cache[1][2]=@cache[1][3]=0.25
  end
  
  def teamsonpoints(teams,round,points)
    (teams*distribution(round,points)).round
  end
  
  def cachestats
    "#{@requests} requests made; #{@cachehits} served from cache; #{@cachehits/@requests} success;"
  end
  
  def cacheinspect(round,points)
    @cache[round][points]
  end

  def distribution(round, points)
    @requests += 1
    #puts "Called distribution with round #{round} and points #{points}"
    if @cache[round][points]!=nil
      #puts "Served from cache"
      @cachehits += 1
      return @cache[round][points]
    end
    if (points < 0) || (round==0)
      0
    elsif (round == 1) && (points <= 3)
      0.25
    else
      returnval = distribution(round-1,points)*0.25+distribution(round-1,points-1)*0.25+distribution(round-1,points-2)*0.25+distribution(round-1,points-3)*0.25
      @cache[round][points]=returnval
      return returnval
    end
  end
end    