#!/usr/bin/python
#begin python cookbook code
def k_subsets_i(n, k):
    '''
    Yield each subset of size k from the set of intergers 0 .. n - 1
    n -- an integer > 0
    k -- an integer > 0
    '''
    # Validate args
    if n < 0:
        raise ValueError('n must be > 0, got n=%d' % n)
    if k < 0:
        raise ValueError('k must be > 0, got k=%d' % k)
    # check base cases
    if k == 0 or n < k:
        yield set()
    elif n == k:
        yield set(range(n))

    else:
        # Use recursive formula based on binomial coeffecients:
        # choose(n, k) = choose(n - 1, k - 1) + choose(n - 1, k)
        for s in k_subsets_i(n - 1, k - 1):
            s.add(n - 1)
            yield s
        for s in k_subsets_i(n - 1, k):
            yield s

def k_subsets(s, k):
    '''
    Yield all subsets of size k from set (or list) s
    s -- a set or list (any iterable will suffice)
    k -- an integer > 0
    '''
    s = list(s)
    n = len(s)
    for k_set in k_subsets_i(n, k):
        yield set([s[i] for i in k_set])
#end python cookbook code

from sys import stdin

class Team:
    
    def __init__(self, id, points, og, oo, cg, co):
        self.id = id
        self.points = points
        self.og = og
        self.oo = oo
        self.cg = cg
        self.co = co
    
    def __repr__(self):
        return "(%s)" % ", ".join(map(str, [self.id, self.points, self.og, self.oo, self.cg, self.co]))


def read():
    teams = []
    ignored = stdin.readline()
    line = stdin.readline()
    while line:
        id, points, og, oo, cg, co = map(int, map(str.strip, line.split("\t")))
        teams.append(Team(id, points, og, oo, cg, co))
        line = stdin.readline()
    return teams

def cmpPoints(teamA, teamB):
	return teamA.points - teamB.points

def baddness(l):
    result = 0
    for e in l:
        diff = (e - min(l))
        if diff > 0:
            result += (diff - 1) ** 2
    return result

def splitInToBrackets(teams):
    brackets = {}
    for team in teams:
        if team.points not in brackets:
            brackets[team.points] = []
        brackets[team.points].append(team)
    return map(lambda (x, y): y, (reversed(sorted(brackets.items()))))

def bruteForceInBrackets(teams):
    brackets = splitInToBrackets(teams)

    uberBracket = []
    i = 0
    while len(uberBracket) < 4:
        uberBracket.extend(brackets[i])
        i += 1
    combinations = k_subsets(uberBracket, 4)
    for combination in combinations:
        print combination
    

bruteForceInBrackets(read())


#print baddness([0, 0, 0, 0])
#print baddness([0, 0, 0, 1])
#print baddness([0, 1, 1, 1])
#print baddness([0, 1, 1, 2])
#print baddness([0, 1, 2, 2])
#print baddness([0, 1, 1, 3])
