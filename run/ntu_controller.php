<?
//Determine action and moduletype and branch accordingly

if (isset ($ntu_override_module)) {
    $moduletype = $ntu_override_module;
} else {
    $moduletype=trim(@$_GET['moduletype']); 
    if (!$moduletype)
        $moduletype=$ntu_default_module;
}

$action=trim(@$_GET['action']);
if (!$action) $action=$ntu_default_action;

if (! isset($title))
    $title = "";

if (in_array($moduletype, $ntu_titles))
    $title .= " " . $ntu_titles[$moduletype] . " ";
else 
    $title = " " . ucfirst($moduletype) . " ";

include("view/header.php");
include("view/mainmenu.php");
include("view/$ntu_controller/menu.php");

//Load respective module
include("$ntu_controller/$moduletype.inc");

include("view/footer.php");
?>
