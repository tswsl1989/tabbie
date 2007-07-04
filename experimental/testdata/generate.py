xs= [0]
xs = sorted(flatten([[x, x + 1, x +2, x +3] for x in xs]))
xs = sorted(flatten([[x, x + 1, x +2, x +3] for x in xs]))
xs = sorted(flatten([[x, x + 1, x +2, x +3] for x in xs]))
xs = sorted(flatten([[x] * 6 for x in xs]))
points = xs
positions = flatten([[i] * 96 for i in range(4)])
for i in range(384):
    empty = sample(positions, 1)[0]
    positions.remove(empty)
    score = sample(points, 1)[0]
    points.remove(score)
    aaa = [1, 1, 1, 1]
    aaa[empty] = 0
    a, b, c, d = aaa
    f.write("\t".join(map(str, [i, score, a,b,c,d])) + "\n")
