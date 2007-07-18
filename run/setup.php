<?php
$ntu_controller = "setup";

require("ntu_bridge.php");
require("view/header.php");
require("view/mainmenu.php");

require_once("includes/display.inc");

$action=trim(@$_GET['action']);
if (!$action) $action="display"; //set to round if empty
$type=trim(@$_GET['type']);
$actionhidden=trim(@$_POST['actionhidden']);
$hiddentype=trim(@$_POST['hiddentype']);
$lowerlimit=trim(@$_POST['lowerlimit']);
$upperlimit=trim(@$_POST['upperlimit']);


if ($actionhidden =="edit") //do validation
{    $validate=1;

    //Check if they are empty and set the validate flag accordingly
    if (!$lowerlimit)
    {    $msg[]="Lower Limit is missing.";
        $validate=0;
    }
    if (!$upperlimit)
    {    $msg[]="Upper Limit is missing.";
        $validate=0;
    }
    if ($lowerlimit<0)
    {    $msg[]="Limits cannot go below 0.";
        $validate=0;
    }
    if ($lowerlimit>$upperlimit)
    {    $msg[]="Upper Limit should be greater than Lower Limit.";
        $validate=0;
    }

    if ($validate==1)
    {    $query = "UPDATE highlight ";
        $query.= "SET lowerlimit='$lowerlimit', upperlimit='$upperlimit' ";
        $query.= "WHERE type='$hiddentype'";
        $result=mysql_query($query);
        if (!$result)
        {    $msg[]="There were some problems editing : ".mysql_error();
            $action="edit";
        }
        else
        {    $msg[]="Record edited successfully.";
            $action="display";
        }
    }
    else
        $action="edit";
}

if ($action=="edit")
{    if ($actionhidden!="edit")
    {    $query="SELECT * FROM highlight WHERE type='$type' ";
        $result=mysql_query($query);
        if (mysql_num_rows($result)==0)
        {    $msg[]="There were some problems editing : Record Not Found ";
            $action="display";
        }
        else
        {    $row=mysql_fetch_assoc($result);
            $lowerlimit=$row['lowerlimit'];
            $upperlimit=$row['upperlimit'];
        }
    }
}

$title = "Setup Module";
switch ($action)
{    case "edit":    $title.=": Edit";
                    break;
    case "display":    $title.=": Display";
                    break;
    default:        $action = "display";
                    $title.=": Display";
                    break;
}

echo "<h2>$title</h2>\n";

displayMessagesUL(@$msg);

if ($action=="display")
{    echo "<table> ";
    echo "<tr><th>Type</th><th>Lower Limit</th><th>Upper Limit</th></tr>";
    $query="SELECT * FROM highlight ";
    $result=mysql_query($query);
    while ($row=mysql_fetch_assoc($result))
    {    echo "<tr><td>{$row['type']}</td><td>{$row['lowerlimit']}</td><td>{$row['upperlimit']}</td> ";
        echo "<td><a href=\"setup.php?type={$row['type']}&amp;action=edit\">Edit</a></td></tr> ";
    }
    echo "</table>";
}
else //action="edit"
{    ?>
    <form action="setup.php" method="POST">
        <input type="hidden" name="actionhidden" value="<?echo $action;?>"/>
        <input type="hidden" name="hiddentype" value="<?echo $type;?>"/>
        <label for="type"><? echo "<h3><br/>Type: $type </h3>"; ?></label><br/>
        <label for="lowerlimit">Lower Limit</label>
        <input type="text" id="lowerlimit" name="lowerlimit" value="<?echo $lowerlimit;?>"/><br/><br/>
        <label for="upperlimit">Upper Limit</label>
        <input type="text" id="upperlimit" name="upperlimit" value="<?echo $upperlimit;?>"/><br/><br/>
        <input type="submit" value="Edit"/>
        <input type="button" value="Cancel" onClick="location.replace('setup.php')"/>
    </form>

    <?
}

require('view/footer.php'); 
?>

