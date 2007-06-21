#!/usr/bin/python
from sys import stdin
from math import ceil

def cmpBadnes(teamA, teamB):
    return teamA.badness() - teamB.badness()

def cmpPoints(teamA, teamB):
    return teamA.points() - teamB.points()

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
    
    def __init__(self, position, team):
        self.position = position
        self.team = team
        
    def badness(self):
        return self.team.relativeBadness()[self.position]
        
class Debate:
    
    def __init__(self, positions):
        self.positions = positions
    
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

def read():
    teams = []
    ignored = stdin.readline()
    line = stdin.readline()
    while line:
        id, points, og, oo, cg, co = map(int, map(str.strip, line.split("\t")))
        teams.append(Team(id, points, [og, oo, cg, co]))
        line = stdin.readline()
    return teams

def justKeepSwapping(teams):
    bla = debatesPerLevel(brackets(teams))
    matrix = Matrix(bla)
    levelsWithDebates = {}
    for bunchOfLevels in matrix.connectedLevels():
        selectedTeams = [team for team in teams if (team.points in bunchOfLevels)]
        selectedTeams = list(reversed(sorted(selectedTeams)))
        for level in bunchOfLevels:
            debates = []
            nrOfDebates = bla.get(level, [0])[0]
            for i in range(nrOfDebates):
                debate = Debate(selectedTeams[:4])
                selectedTeams = selectedTeams[4:]
                debates.append(debate)
            if not level in levelsWithDebates:
                levelsWithDebates[level] = []
            levelsWithDebates[level].append(debate)
        partialSolution = Solution(levelsWithDebates)
        
        debates = sorted(partialSolution.debates(), cmpBadnes)
        worst = debates.pop()
        print worst.badness()
        print "SCORE", partialSolution.badness()
        
    
if __name__ == "__main__":
    justKeepSwapping(read())

