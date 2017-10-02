<?php
$db = new PDO('sqlite:'.dirname(__FILE__).'/../database.sqlite3', SQLITE3_OPEN_READWRITE);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
