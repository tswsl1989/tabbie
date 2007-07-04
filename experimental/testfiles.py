#!/usr/bin/python

import os
import draw
from time import time

if __name__ == "__main__":
    for name in [name for name in sorted(os.listdir('testdata')) if name.endswith(".tsv")]:
        filename = "testdata/" + name
        f = open(filename)
        try:
            start = time()
            print filename
            teams = draw.read(f)
            result = draw.justKeepSwapping(teams)
            if not draw.validate(teams, result):
                print "Error validating file", filename
            expected = int(name.split("_")[0])
            score = draw.score(result)
            if not expected == score:
                print "Unexpected score", score, filename
                for team in teams:
                    print team
                for debate in result:
                    print debate
            print "in %s seconds" % (time() - start)
        finally:
            f.close
    print "DONE"
        