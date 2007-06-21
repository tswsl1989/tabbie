#!/usr/bin/python
from sys import stdin
from math import ceil

def cmpBadness(something, somethingElse):
    return something.badness() - somethingElse.badness()

def cmpPoints(teamA, teamB):
    return teamA.points - teamB.points

def twoparts(x):
    return x / 2, x / 2 + x % 2

def badness(l):
    def f(L1, L2):
        return sum([(l1 - l2) ** 2 for l1, l2 in zip(L1, L2)])
    x, y = twoparts(sum(l))
    a, b = twoparts(x)
    c, d = twoparts(y)
    ideal = sorted([a, b, c, d])
    lo = sum(l) / 4
    compare = [lo, lo, lo, lo]
    return (f(compare, sorted(l)) - f(ideal, compare)) ** 2
    
def plusPos(l, i):
    l2 = l[:]
    l2[i] += 1
    return l2

class Team:
    
    def __init__(self, id, points, positions):
        self.id = id
        self.points = points
        self.positions = positions
    
    def __repr__(self):
        return "Team: (%s, %s, %s)" % (self.id, self.points, self.positions)
    
    def badness(self):
        return badness(self.positions)
    
    def relativeBadness(self):
        result = [badness(plusPos(self.positions, position)) for position in range(4)]
        if min(result) != 0:
            lo = min(result)
            result = [x - lo for x in result]
        return result
        
class PositionedTeam:
    
    def __init__(self, team, position, debate):
        self.team = team
        self.position = position
        self.debate = debate
        
    def badness(self):
        return self.team.relativeBadness()[self.position]
    
    def __repr__(self):
        return "(TiP: %s, %s)" % (self.team, self.position)
        
class Debate:
    
    def __init__(self, positions, level):
        self.positions = positions
        self.level = level
    
    def badness(self):
        matrix = [team.positions[:] for team in self.positions]
        for i in range(3):
            matrix[i][i] += 1
        return sum([badness(l) for l in matrix])

    def __repr__(self):
        return "Debate: (%s)" % self.positions
    
def brackets(teams):
    result = {}
    for team in teams:
        if team.points not in result:
            result[team.points] = set()
        result[team.points].add(team)
    return result

def debatesPerLevel(brackets):
    missing = 0
    missingOnLevel = 999
    result = {}
    for level, teams in reversed(sorted(brackets.items())):
        teamsLeftOnThisLevel = len(teams)
        if missing:
            teamsFromThisLevel = min(missing, len(teams))
            missing -= teamsFromThisLevel
            teamsLeftOnThisLevel = len(teams) - teamsFromThisLevel
            result[missingOnLevel][1].append((level, teamsFromThisLevel))
        if teamsLeftOnThisLevel:
            result[level] = (int(ceil(1.0 * teamsLeftOnThisLevel / 4)), [(level, teamsLeftOnThisLevel)])
            if teamsLeftOnThisLevel % 4:
                missing = 4 - (teamsLeftOnThisLevel % 4)
                missingOnLevel = level
    return result

class Matrix:
    
    def __init__(self, debatesPerLevel):
        self.debatesPerLevel = debatesPerLevel
        self.data = {}
        for level, (debates, l) in debatesPerLevel.items():
            self.data[level] = [debates for x in range(4)]
            
    def freeAtLevel(self, level):
        if not level in self.data:
            return [0 for x in range(4)]
        return self.data[level]
    
    def freeAtPosition(self, level, position):
        return self.freeAtLevel[position]
    
    def connectedLevels(self):
        result = []
        currentConnection = set([])
        for level, (nrOfDebates, l) in reversed(sorted(self.debatesPerLevel.items())):
            if not level in currentConnection and currentConnection:
                result.append(list(reversed(sorted(currentConnection))))
                currentConnection = set([])
            currentConnection.update([level for level, teams in l])
        if currentConnection:
            result.append(list(reversed(sorted(currentConnection))))
        return result
    
    def set(level, position):
        self.data[level][position] -= 1
        
    def unset(level, position):
        self.data[level][position] += 1

class Solution:
    
    def __init__(self, levelsWithDebates):
        self.levelsWithDebates = levelsWithDebates
        
    def debates(self):
        result = []
        for debateList in self.levelsWithDebates.values():
            result.extend(debateList)
        return result
        
    def badness(self):
        return sum([debate.badness() for debate in self.debates()])
    
    def __repr__(self):
        return "\n" . join([str(debate) for debate in self.debates()])
    
    def teamsInPosition(self):
        result = []
        for debate in self.debates():
            for position, team in enumerate(debate.positions):
                result.append(PositionedTeam(team, position, debate))
        return result
            
class Solution2:
    
    def __init__(self, debates):
        self.debates = debates
        
    def badness(self):
        return sum([debate.badness() for debate in self.debates])
    
    def __repr__(self):
        return "\n" . join([str(debate) for debate in self.debates])
    
    def teamsInPosition(self):
        result = []
        for debate in self.debates:
            for position, team in enumerate(debate.positions):
                result.append(PositionedTeam(team, position, debate))
        return result

def read():
    teams = []
    ignored = stdin.readline()
    line = stdin.readline()
    while line:
        id, points, og, oo, cg, co = map(int, map(str.strip, line.split("\t")))
        teams.append(Team(id, points, [og, oo, cg, co]))
        line = stdin.readline()
    return teams

def isSwappable(positionedTeam1, positionedTeam2):
    return positionedTeam1.team.points == positionedTeam2.team.points or \
        positionedTeam1.debate.level == positionedTeam2.debate.level

def swapTwoTeams(teamInPositionA, teamInPositionB):
    teamInPositionA.debate.positions[teamInPositionA.position] = teamInPositionB.team
    teamInPositionB.debate.positions[teamInPositionB.position] = teamInPositionA.team

def justKeepSwapping(teams):
    bla = debatesPerLevel(brackets(teams))
    matrix = Matrix(bla)
    levelsWithDebates = {}
    result = []
    for bunchOfLevels in matrix.connectedLevels():
        #init
        selectedTeams = [team for team in teams if (team.points in bunchOfLevels)]
        selectedTeams = list(reversed(sorted(selectedTeams, cmp = cmpPoints)))
        debates = []
        while selectedTeams:
            debates.append(Debate(selectedTeams[:4], selectedTeams[0].points))
            selectedTeams = selectedTeams[4:]
        partialSolution = Solution2(debates)
        nullSwapAttempts = 100
        while True:
            if partialSolution.badness() == 0:
                break
            positionedTeams = sorted(partialSolution.teamsInPosition(), cmpBadness)
            nullSwappers = []
            while positionedTeams:
                worst = positionedTeams.pop()
                possibleSwappers = [team for team in positionedTeams if isSwappable(team, worst)]
                
                bestEffect = 0
                bestSwapper = None
                for swapper in possibleSwappers:
                    currentBadness = worst.badness() + swapper.badness()
                    f1 = PositionedTeam(worst.team, swapper.position, swapper.debate)
                    f2 = PositionedTeam(swapper.team, worst.position, worst.debate)
                    futureBadness = f1.badness() + f2.badness()
                    netEffect = currentBadness - futureBadness
                    if netEffect == 0:
                        nullSwappers.append((worst, swapper))
                    if netEffect > bestEffect:
                        bestSwapper = swapper
                if bestSwapper:
                    swapTwoTeams(bestSwapper, worst)
                    nullSwapAttempts = 100
                    break
            else:
                if nullSwapAttempts > 0 and nullSwappers:
                    from random import sample
                    a, b = sample(nullSwappers, 1)[0]
                    swapTwoTeams(a, b)
                    nullSwapAttempts -= 1
                else:
                    break
        result.extend(partialSolution.debates)
    return result
    
if __name__ == "__main__":
    teams = read()
    print "Teams: "
    for team in teams:
        print team
    debates = justKeepSwapping(teams)
    print "SCORE", Solution2(debates).badness()
    for debate in debates:
        print debate
        

#TODO:
# multiple step optimalization
# checking for errors