#!/usr/bin/python
import unittest
from draw import *

class DrawTest(unittest.TestCase):
    
    def testBadness(self):
        x = ignored = 0
        self.assertEquals([0, 0, 0, 0], [team.badness() for team in [
            Team(x, x, [0, 0, 0, 0]),
            Team(x, x, [9, 9, 9, 9]),
            Team(x, x, [0, 0, 1, 1]),
            Team(x, x, [0, 1, 1, 1])]])
        
        l = [team.badness() for team in [
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [0, 1, 1, 2]),
            Team(x, x, [0, 0, 1, 2]),
            Team(x, x, [0, 0, 0, 2]),
            Team(x, x, [0, 1, 1, 3]),
            Team(x, x, [0, 1, 2, 3])]]
        prev = -1
        for e in l:
            self.assertTrue(e > prev, "%s is not strictly growing" %l)
            prev = e
            
    def testDebateBadness(self):
        x = ignored = 0
        debate = Debate([
            Team(x, x, [0, 1, 1, 1]),
            Team(x, x, [1, 0, 1, 1]),
            Team(x, x, [1, 1, 0, 1]),
            Team(x, x, [1, 1, 1, 0])])
        self.assertEquals(0, debate.badness())
        
        debate = Debate([
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1])])
        self.assertEquals(0, debate.badness())

        debate = Debate([
            Team(x, x, [2, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1])])
        self.assertTrue(debate.badness() > 0)

    def testSolutionBadness(self):
        x = ignored = 0
        debate = Debate([
            Team(x, x, [2, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1])])
        solution = Solution({1: [debate, debate]})
        self.assertEquals(2 * debate.badness(), solution.badness())

    def testGetBrackets(self):
        x = ignored = 0
        team0, team1, team2, team3 = teams = [
                 Team(0, 0, [x, x, x, x]),
                 Team(1, 0, [x, x, x, x]),
                 Team(2, 0, [x, x, x, x]),
                 Team(3, 0, [x, x, x, x])]
        self.assertEquals(
            {0: set([team0, team1, team2, team3])}, brackets(teams))
        
        team0, team1, team2, team3 = teams = [
                 Team(0, 0, [x, x, x, x]),
                 Team(1, 0, [x, x, x, x]),
                 Team(2, 1, [x, x, x, x]),
                 Team(3, 2, [x, x, x, x])]
        self.assertEquals(
            {0: set([team0, team1]), 1: set([team2]), 2: set([team3])}, brackets(teams))
        
    def testDebatesPerLevel(self):
        x = ignoredTeam = "Team"
        brackets = {0: [x, x, x, x]}
        self.assertEquals({0: (1, [(0, 4)])}, debatesPerLevel(brackets))
        
        brackets = {0: [x, x], 1: [x], 2: [x]}
        self.assertEquals({2: (1, [(2, 1), (1, 1), (0, 2)])}, debatesPerLevel(brackets))

        brackets = {0: [x, x], 1: [x], 2: [x]}
        self.assertEquals({2: (1, [(2, 1), (1, 1), (0, 2)])}, debatesPerLevel(brackets))
        
        brackets = {0: [x, x],
                    1: [x],
                    2: [x],
                    3: [x, x, x, x],
                    4: [x, x, x, x, x, x],
                    5: [x, x, x, x, x, x, x, x],
                    6: [x, x, x, x, x ,x],
                    7: [x, x, x, x]}
        
        self.assertEquals({
            7: (1, [(7, 4)]),
            6: (2, [(6, 6), (5, 2)]),
            5: (2, [(5, 6), (4, 2)]),
            4: (1, [(4, 4)]),
            3: (1, [(3, 4)]),
            2: (1, [(2, 1), (1, 1), (0, 2)])
            }, debatesPerLevel(brackets))
    
    def testMatrix(self):
        debatesPerLevel = {}
        matrix = Matrix(debatesPerLevel)
        self.assertEquals([0, 0, 0, 0], matrix.freeAtLevel(0))
        
        debatesPerLevel = {0: (1, [(0, 4)])}
        matrix = Matrix(debatesPerLevel)
        self.assertEquals([1, 1, 1, 1], matrix.freeAtLevel(0))
        
        debatesPerLevel = {1: (1, [(1, 4)]), 0: (2, [(0, 8)])}
        matrix = Matrix(debatesPerLevel)
        self.assertEquals([1, 1, 1, 1], matrix.freeAtLevel(1))
        self.assertEquals([2, 2, 2, 2], matrix.freeAtLevel(0))
    
    def testconnectedLevels(self):
        debatesPerLevel = {}
        matrix = Matrix(debatesPerLevel)
        self.assertEquals([], matrix.connectedLevels())
        
        debatesPerLevel = {0: (1, [(0, 4)])}
        matrix = Matrix(debatesPerLevel)
        self.assertEquals([[0]], matrix.connectedLevels())
        
        debatesPerLevel = {1: (1, [(1, 4)]), 0: (1, [(0, 4)])}
        matrix = Matrix(debatesPerLevel)
        self.assertEquals([[1], [0]], matrix.connectedLevels())
        
        debatesPerLevel = {1: (2, [(1, 5), (0, 3)]), 0: (1, [(0, 4)])}
        matrix = Matrix(debatesPerLevel)
        self.assertEquals([[1, 0]], matrix.connectedLevels())
        
        debatesPerLevel = {1: (2, [(1, 5), (0, 3)]), 0: (1, [(0, 4)])}
        matrix = Matrix(debatesPerLevel)
        self.assertEquals([[1, 0]], matrix.connectedLevels())
        
        debatesPerLevel = {5: (1, [(5, 2), (4, 2)])}
        matrix = Matrix(debatesPerLevel)
        self.assertEquals([[5, 4]], matrix.connectedLevels())
        

        

if __name__ == "__main__":
    unittest.main()