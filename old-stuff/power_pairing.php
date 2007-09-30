<?php /* begin license *
 * 
 *     Tabbie, Debating Tabbing Software
 *     Copyright Contributors
 * 
 *     This file is part of Tabbie
 * 
 *     Tabbie is free software; you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation; either version 2 of the License, or
 *     (at your option) any later version.
 * 
 *     Tabbie is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 * 
 *     You should have received a copy of the GNU General Public License
 *     along with Tabbie; if not, write to the Free Software
 *     Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * end license */

$title = "Tabbie - BP Debating Tab Software - Draw Algorithms";
$dir_hack = "run/";
require("run/view/header.php");
$local = ($_SERVER["SERVER_NAME"] != "tabbie.sourceforge.net");
?>

<div id="mainmenu">
    <h2 class="hide">Main Menu</h2>
    <ul>
    <li><a href="index.php">Home</a></li>
    <li><a href="http://sourceforge.net/project/showfiles.php?group_id=199347">Download</a></li>
    <li><a href="installation.php">Installation Guide</a></li>
    <li><a href="contributors.php">Contributors</a></li>
    <li><a href="run/">Run<? if (!$local) echo " Online Demo"; ?></a></li>
    </ul>
</div>

<h3>Thoughts on Tournament Systems.</h3>
<p>
This is an attempt ('article') to formulate some thoughts surrounding British Parliamentary Debating Tournament Tab Systems, i.e. the allocation of teams to debates, and the appreciation of their achievements in terms of the final ranking. Throughout my debating carreer I've been surprised by the lack of structured thought and debate surrounding these issues and the great amount of mystical statements and figures - this is an attempt to change this. It is also an attempt to open debate on the different forms and to discover if some are strictly better than others (under clearly defined goals and assumptions). It is very much work in progress.
</p><p>
The main ideas in this article should be understandable with some effort by anyone who has the intellectual level to participate in debating, and who has a basic understanding of the rules of BP debating. With any tournament run by some system, it should also be an issue of concern to exactly those people, and specifically to anyone involved in organizing tournaments. I am not much of a statistical wizard myself, so don't expect a very formal approach; a more formal response would be much appreciated.
</p><p>
Firstly, I will state a number of clear goals for any Tournament System, and clarify some definitions. I will explore some forms of Power Pairing I've heard about, and make (somewhat funded) statements about their relative efficacy at attaining these goal. Furthermore, I will present some observations that may be useful for future implementors of such systems or the general public.
</p><p>
<h4>Goals:</h4>
<p>
The first and formost goal of a Tournament System is to determine the order of the teams on the final ranking. It seems obvious to me that determining the winner correctly is more important than determining the second place, and so forth until the last place. Also, if there are any intermediate results, such as making it to the semi finals or finals, determining the correct teams for these results is of some importance, and having the right teams in the more important rounds is more important.
</p><p>
There can be many secondary goals for a Tournament System, such as creating debates that are the most fun or chalenging, the system itself being comprehensable by the competitors and creating the exciting atmosphere of a tournament. These are important goals that may be given different individual weight depending on the tastes of the organizors. It is conceivable that for some tournaments some of these considerations even prevale over determining the correct winner.
</p><p>
Having the best team win and everyone having a good time depends on many things: the quality and balance of the motions, the quality of the adjudications panels, the allocation of judges to different debates and the amount of free alcohol served to name a few. However, I will make a case here that choosing a Tournament System is important enough to be discussed openly and scientiffically, and not, as is happening too often now, in secrecy and in almost mystical terms. Also, the allocation of judges to debates will be discussed seperately.
</p>
<h4>Definitions</h4>
<p>
This article is concerned with proving some sensible basis for the Tournament Systems that are actually in use. These systems have a number of properties:
<ul>
<li>The tournament consists of two phases, divided by a so called "Break". Before the break everyone participates in some pre-break system. After the break the best 4, 8, 16, 32, ... teams continue to a final, semi-final, quarters etc.</li>
<li>There may be a seperate break for certain teams, i.e. teams which speak English as a second language, or are composed of novices</li>
<li>Before the break, the teams are awarded 3 points for first place, 2 points for second, 1 for third place and 0 for fourth place, or any other number that can easily be expressed in these terms. (i.e. a system that uses 4 points for winning can be expressed by substracting one point for each round)</li>
<li>The speakers, and by extension the teams, are also awarded speaker's points. Teams with more speaker's points in a specific debate also recieve a higher ranking in that debate.</li>
<li>The break is determined by ordering the teams, first on the total number of acquired points, and second on their total number of speaker's points. If there is still a tie between two teams this tie is broken by some other mechanism, and finaly by chance.</li>
</ul>
In fact, it remains to be seen if these properties actually provide the best possible basis for a Tournament System, but this is not the focus of this article.




What way to fold the break
Bracket

What's the role of Swing teams?



Issues:
Relevance of speaking positions' winning chances.
How big should your break be? Should you even have a break at all?

Which teams should be pulled up?
Should you use speaker's points?

Should you have a fold or bubble?

</p><p>
Copyright Klaas van Schelven, GPL 2.0.
</p>

<?php require("run/view/footer.php"); ?>
