<?php

if (file_exists("config/settings.php")) {
    require("index2.php");
} else {
    require("install.php");
}

?>