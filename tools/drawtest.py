#!/usr/bin/python
import unittest
from draw import *

class DrawTest(unittest.TestCase):
    
    def testBadness(self):
        """Badness is an important operator of our algorithm.
        
        Notes.:
        Badness comparisons between lists of different sums are irrelant, 
        because they are not used in practice"""
        def check(l):
            prev = -1
            for e in l:
                self.assertTrue(e > prev, "%s is not strictly growing" %l)
                prev = e
        
        x = ignored = 0
        self.assertEquals([0, 0, 0, 0], [badness(l) for l in [
            [0, 0, 0, 0],
            [9, 9, 9, 9],
            [0, 0, 1, 1],
            [0, 1, 1, 1]]])
        
        check([badness(l) for l in [[0, 0, 1, 1], [0, 0, 0, 2]]])
        check([badness(l) for l in [[0, 1, 1, 1], [0, 0, 1, 2], [0, 0, 0, 3]]])
        check([badness(l) for l in [[1, 1, 1, 1], [0, 1, 1, 2], [0, 0, 2, 2], [0, 0, 1, 3], [0, 0, 0, 4]]])
        check([badness(l) for l in [[1, 1, 1, 2], [0, 1, 2, 2], [0, 1, 1, 3], [0, 0, 2, 3], [0, 0, 1, 4], [0, 0, 0, 5]]])
        
        #relative differences increase as well...
        check([badness(b) - badness(a) for a, b in [
            ([1, 1, 1, 1], [0, 1, 1, 2]),
            ([0, 1, 1, 2], [0, 0, 2, 2]),
            ([0, 0, 2, 2], [0, 0, 1, 3]),
            ([0, 0, 1, 3], [0, 0, 0, 4])]])
    
    def testRelativeBadness(self):
        x = ignored = 0
        self.assertEquals([0, 0, 0, 0], Team(x, x, [0, 0, 0, 0]).relativeBadness())
        self.assertEquals([4, 0, 0, 0], Team(x, x, [1, 0, 0, 0]).relativeBadness())
        self.assertEquals([0, 4, 4, 16], Team(x, x, [0, 1, 1, 2]).relativeBadness())
        self.assertEquals([0, 12, 32, 60], Team(x, x, [0, 1, 2, 3]).relativeBadness())
    
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
        
    def testSelectTeamsOrderedByBadness(self):
        x = ignored = 0
        solution = Solution({1: [Debate([
            Team(0, x, [0, 1, 1, 1]),
            Team(1, x, [1, 1, 0, 1]),
            Team(2, x, [0, 0, 2, 1]),
            Team(3, x, [0, 0, 0, 3])])]})
        result = solution.teamsInPosition()
        
    def testTeamInPosition(self):
        x = ignored = 0
        positionedTeam = PositionedTeam(0, Team(x, x, [0, 1, 1, 1]))
        self.assertEquals(0, positionedTeam.badness())
        positionedTeam = PositionedTeam(0, Team(x, x, [1, 1, 1, 0]))
        self.assertEquals(Team(x, x, [2, 1, 1, 0]).badness(), positionedTeam.badness())

if __name__ == "__main__":
    unittest.main()