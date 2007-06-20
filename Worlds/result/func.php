<?
function returnposition($first, $second,$third,$fourth,$team_id)
{
    if ($team_id==$first) 
        return 1;
    elseif ($team_id==$second) 
        return 2;
    elseif ($team_id==$third) 
        return 3;
    elseif ($team_id==$fourth) 
        return 4;
    else
    return "<b>none</b>";    

}
?>
