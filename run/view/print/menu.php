<div id="submenu">
<h2 class="hide">Input Submenu</h2>
<ul>
    <li><a href="print.php?moduletype=main" <?echo ($moduletype=="main")?"class=\"activemain\"":""?>>main</a></li>
    <li><a href="print.php?moduletype=adjudicator" <?echo ($moduletype=="adjudicator")?"class=\"activemain\"":""?>>adjudicator</a></li>
    <li><a href="print.php?moduletype=floor" <?echo ($moduletype=="floor")?"class=\"activemain\"":""?>>floor managers</a></li>
    <li><a href="print.php?moduletype=chfadjud" <?echo ($moduletype=="chfadjud")?"class=\"activemain\"":""?>>CA/DCAs</a></li>
    <li><a href="print.php?moduletype=tab" <?echo ($moduletype=="tab")?"class=\"activemain\"":""?>>Tab Room</a></li>
    <li><a href="print.php?moduletype=display" <?echo ($moduletype=="display")?"class=\"activemain\"":""?>>Display</a></li>
</ul>
</div>
