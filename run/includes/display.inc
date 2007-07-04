<?

function displayMessagesUL($messages) {
    if (count($messages) > 0) {
        echo "<ul class=\"err\">\n";
        foreach ($messages as $message)
            echo "<li>$message</li>\n";
        echo "</ul>";
    }
}

function displayMessagesP($messages) {
    for($x = 0; $x < count($messages); $x++)
        echo "<p class=\"err\">".$messages[$x]."</p>\n";
}

?>