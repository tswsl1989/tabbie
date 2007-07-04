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
        self.assertEquals([0, 0, 0, 0], Team(x, x, [0, 0, 0, 0]).relativeBadnesses())
        self.assertEquals([4, 0, 0, 0], Team(x, x, [1, 0, 0, 0]).relativeBadnesses())
        self.assertEquals([0, 4, 4, 16], Team(x, x, [0, 1, 1, 2]).relativeBadnesses())
        self.assertEquals([0, 12, 32, 60], Team(x, x, [0, 1, 2, 3]).relativeBadnesses())
    
    def testDebateBadness(self):
        x = ignored = 0
        debate = Debate([
            Team(x, x, [0, 1, 1, 1]),
            Team(x, x, [1, 0, 1, 1]),
            Team(x, x, [1, 1, 0, 1]),
            Team(x, x, [1, 1, 1, 0])], x)
        self.assertEquals(0, debate.badness())
        
        debate = Debate([
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1])], x)
        self.assertEquals(0, debate.badness())

        debate = Debate([
            Team(x, x, [2, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1])], x)
        self.assertTrue(debate.badness() > 0)

    def testSolutionBadness(self):
        x = ignored = 0
        debate = Debate([
            Team(x, x, [2, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1]),
            Team(x, x, [1, 1, 1, 1])], x)
        solution = Solution([debate, debate])
        self.assertEquals(2 * debate.badness(), solution.badness())

    def testSelectTeamsInPosition(self):
        x = ignored = 0
        team0, team1, team2, team3 = teams = [
            Team(0, x, [0, 1, 1, 1]),
            Team(1, x, [1, 1, 0, 1]),
            Team(2, x, [0, 0, 2, 1]),
            Team(3, x, [0, 0, 0, 3])]
        solution = Solution([Debate(teams, x)])
        result = solution.teamsInPosition()
        self.assertEquals(team3, result[3].team)
        
    def testTeamInPosition(self):
        x = ignored = 0
        positionedTeam = PositionedTeam(Team(x, x, [0, 1, 1, 1]), 0, x)
        self.assertEquals(0, positionedTeam.badness())
        positionedTeam = PositionedTeam(Team(x, x, [1, 1, 1, 0]), 0, x)
        self.assertEquals(Team(x, x, [2, 1, 1, 0]).badness(), positionedTeam.badness())
        
    def testValidate(self):
        x = ignored = 0
        teams0 = [
            Team(0, 3, [x, x, x, x]),
            Team(1, 3, [x, x, x, x]),
            Team(2, 2, [x, x, x, x]),
            Team(3, 2, [x, x, x, x])]
        teams1 = [
            Team(4, 1, [x, x, x, x]),
            Team(5, 1, [x, x, x, x]),
            Team(6, 1, [x, x, x, x]),
            Team(7, 0, [x, x, x, x])]
        teams2 = [
            Team(8, 1, [x, x, x, x]),
            Team(9, 1, [x, x, x, x]),
            Team(10, 1, [x, x, x, x]),
            Team(11, 0, [x, x, x, x])]
        teams4 = [
            Team(12, 3, [x, x, x, x]),
            Team(13, 3, [x, x, x, x]),
            Team(14, 2, [x, x, x, x]),
            Team(15, 2, [x, x, x, x])]
        
        self.assertTrue(validate(teams0 + teams1, [Debate(teams0), Debate(teams1)]))
        
        self.assertFalse(validate(teams0 + teams1, [Debate(teams1), Debate(teams0)]))
        self.assertTrue(validate(teams1 + teams2, [Debate(teams1), Debate(teams2)]))
        self.assertFalse(validate(teams0 + teams4, [Debate(teams0), Debate(teams4)]))
        
        #input != output
        self.assertFalse(validate(teams1 + teams2, [Debate(teams0), Debate(teams1)]))


if __name__ == "__main__":
    unittest.main()