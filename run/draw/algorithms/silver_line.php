<?
/*

#!/usr/bin/python
from sys import stdin
from math import ceil

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
        self._badness = None
        self._futureBadnesses = None
        self._relativeBadnesses = None
    
    def __repr__(self):
        return "Team: (%s, %s, %s)" % (self.id, self.points, self.positions)
    
    def badness(self):
        if not self._badness:
            self._badness = badness(self.positions)
        return self._badness
        
    def futureBadnesses(self):
        if not self._futureBadnesses:
            self._futureBadnesses = [badness(plusPos(self.positions, position)) for position in range(4)]
        return self._futureBadnesses
    
    def relativeBadnesses(self):
        if not self._relativeBadnesses:
            self._relativeBadnesses = self.futureBadnesses()
            if min(self._relativeBadnesses) != 0:
                lo = min(self._relativeBadnesses)
                self._relativeBadnesses = [x - lo for x in self._relativeBadnesses]
        return self._relativeBadnesses
        
class PositionedTeam:
    
    def __init__(self, team, position, debate):
        self.team = team
        self.position = position
        self.debate = debate
        
    def badness(self):
        return self.team.futureBadnesses()[self.position]
    
    def relativeBadness(self):
        return self.team.relativeBadnesses()[self.position]
    
    def __repr__(self):
        return "(TiP: %s, %s)" % (self.team, self.position)
        
class Debate:
    
    def __init__(self, positions, level = None):
        self.positions = positions
        self.level = level
    
    def badness(self):
        return sum([team.futureBadnesses()[i] for i, team in enumerate(self.positions)])

    def relativeBadness(self):
        return sum([team.relativeBadnesses()[i] for i, team in enumerate(self.positions)])

    def __repr__(self):
        return "Debate: (%s)" % self.positions
    
    def __eq__(self, other):
        if other.__class__ != Debate:
            return False
        if other.positions != self.positions:
            return False
    
class Solution:
    
    def __init__(self, debates):
        self.debates = debates
        
    def badness(self):
        return sum([debate.badness() for debate in self.debates])
    
    def relativeBadness(self):
        return sum([debate.relativeBadness() for debate in self.debates])
    
    def __repr__(self):
        return "\n" . join([str(debate) for debate in self.debates])
    
    def teamsInPosition(self):
        result = []
        for debate in self.debates:
            for position, team in enumerate(debate.positions):
                result.append(PositionedTeam(team, position, debate))
        return result

def read(f):
    teams = []
    ignored = f.readline()
    line = f.readline()
    while line:
        id, points, og, oo, cg, co = map(int, map(str.strip, line.split("\t")))
        teams.append(Team(id, points, [og, oo, cg, co]))
        line = f.readline()
    return teams

def isSwappable(positionedTeam1, positionedTeam2):
    return positionedTeam1 != positionedTeam2 and \
           (positionedTeam1.team.points == positionedTeam2.team.points or \
           positionedTeam1.debate.level == positionedTeam2.debate.level)

def swapTwoTeams(teamInPositionA, teamInPositionB):
    debateA = teamInPositionA.debate
    debateB = teamInPositionB.debate
    positionA = teamInPositionA.position
    positionB = teamInPositionB.position
    debateA.positions[positionA] = teamInPositionB.team
    debateB.positions[positionB] = teamInPositionA.team
    teamInPositionA.position = positionB
    teamInPositionB.position = positionA
    teamInPositionA.debate = debateB
    teamInPositionB.debate = debateA

def debatesFromTeams(teams):
    teams = list(reversed(sorted(teams, cmp = cmpPoints)))
    result = []
    while teams:
        result.append(Debate(teams[:4], teams[0].points))
        teams = teams[4:]
    return result
    
def findABestSwapFor(positionedTeams, teamA, effectTillNow=0, takePerfection=True):
    bestEffect = 0
    bestTeamB = None
    for teamB in positionedTeams: #this loop especially can be limited
        if isSwappable(teamA, teamB):
            current = teamA.relativeBadness() + teamB.relativeBadness()
            future = teamA.team.relativeBadnesses()[teamB.position] + \
                    teamB.team.relativeBadnesses()[teamA.position]
            if takePerfection and future == 0:
                swapTwoTeams(teamA, teamB)
                bestTeamB = None
                return True
            netEffect = future - current + effectTillNow
            if netEffect < bestEffect:
                bestEffect = netEffect
                bestTeamB = teamB
    if bestTeamB:
        swapTwoTeams(teamA, bestTeamB)
        return True

def justKeepSwapping(teams):
    debates = debatesFromTeams(teams)
    solution = Solution(debates)
    positionedTeams = solution.teamsInPosition()
    previousSolution = None
    depth = 2
    while solution.relativeBadness() > 0:
        if previousSolution == solution.relativeBadness():
            break
        previousSolution = solution.relativeBadness()
        for teamA in positionedTeams[:]:
            if teamA.relativeBadness() > 0:
                findABestSwapFor(positionedTeams, teamA)
    return solution.debates

def pullUpCount(teams):
    levelDicts = {}
    result = []
    teams = list(reversed(sorted(teams, cmpPoints)))
    while teams:
        level = teams[0].points
        if not level in levelDicts:
            levelDicts[level] = {}
        levelDict = levelDicts[level]
        for team in teams[:4]:
            if not team.points in levelDict:
                levelDict[team.points] = 0
            levelDict[team.points] += 1
        result.append(levelDict)
        teams = teams[4:]
    return result

def validate(teams, debates):
    teamsInDebates = []
    pullUpCounts = pullUpCount(teams)
    for i, debate in enumerate(debates):
        teamsInDebates.extend(debate.positions)
        for team in debate.positions:
            if not team.points in pullUpCounts[i]:
                return False
            pullUpCounts[i][team.points] -= 1
    return set(teamsInDebates) == set(teams)

def score(debates):
    return Solution(debates).badness()

if __name__ == "__main__":
    teams = read(stdin)
    print "Teams: "
    for team in teams:
        print team
    debates = justKeepSwapping(teams)
    print "Score:", score(debates)
    if not validate(teams, debates):
        print "ERROR ERROR ERROR"
    for debate in debates:
        print debate

*/
?>