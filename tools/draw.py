#!/usr/bin/python
from sys import stdin
from math import ceil

def badness(l):
    result = 0
    for e in l:
        diff = (e - min(l))
        if diff > 0:
            result += (diff - 1) ** 2
    if result > 0:
        result += l.count(min(l))
    return result

class Team:
    
    def __init__(self, id, points, positions):
        self.id = id
        self.points = points
        self.positions = positions
    
    def __repr__(self):
        return "Team: (%s, %s, %s)" % (self.id, self.points, self.positions)
    
    def badness(self):
        return badness(self.positions)
        
class Debate:
    
    def __init__(self, positions):
        self.positions = positions
    
    def badness(self):
        matrix = [team.positions for team in self.positions]
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

def read():
    teams = []
    ignored = stdin.readline()
    line = stdin.readline()
    while line:
        id, points, og, oo, cg, co = map(int, map(str.strip, line.split("\t")))
        teams.append(Team(id, points, [og, oo, cg, co]))
        line = stdin.readline()
    return teams

if __name__ == "__main__":
    algorithmOne(read())


#=>

#7 => 1 debate, 4 places @level 7
#6 => 2 deabte, 6 places @level 6
#5 => 2 debates, 2 places @level 6, 2 debates, 6 places @level 5
#4 => 2 debates, 2 places @ level 5, 1 debate, 4 places @ level 4
#3 => 1 debate, 4 places @ level 3
#2 => 1 debate, 2 places @ level 2
#1 => 1 debate, 2 places @ level 2

#=>

#order by badness.
#take highest badness first. reduce by moving to 
