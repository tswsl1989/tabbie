<?

function displayMessages($messages) {
    if (count($messages) > 0) {
        echo "<ul class=\"err\">\n";
        foreach ($msg as $message)
            echo "<li>$message</li>\n";
        echo "</ul>";
    }
}

?>