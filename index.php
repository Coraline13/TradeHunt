<?php
$db = new SQLite3('database.sqlite3', SQLITE3_OPEN_READWRITE);

$results = $db->query('SELECT * FROM users');
while ($row = $results->fetchArray()) {
    var_dump($row);
}

echo "Database read success<br/>";
