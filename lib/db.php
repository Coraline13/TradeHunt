<?php
require_once dirname(__FILE__).'/log.php';
require_once dirname(__FILE__).'/sql_parse.php';

$db = new PDO('sqlite:'.dirname(__FILE__).'/../data/database.sqlite3', SQLITE3_OPEN_READWRITE);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

define("SCHEMA_VERSION", 1);

/**
 * @param $db PDO opened database connection
 * @param $sql string SQL script content (multiple statements delimited by ';'
 */
function exec_sql_script($db, $sql) {
    $sql = remove_comments($sql);
    $sql = remove_remarks($sql);
    $stmts = split_sql_file(trim($sql), ';');
    foreach ($stmts as $stmt) {
        $db->exec($stmt);
    }
}

$schema_version = 0;
try {
    $schema = $db->query("SELECT MAX(version) FROM schema");
    $schema_version = $schema->fetchColumn(0);
}
catch (PDOException $e) {
    log_warning("failed to read schema version from database, recreating");
    $schema_sql = file_get_contents(dirname(__FILE__).'/../schema.sql');
    $db->beginTransaction();
    exec_sql_script($db, $schema_sql);
}

if ($schema_version < SCHEMA_VERSION) {
    log_info("schema version $schema_version is older than ".SCHEMA_VERSION.". upgrading...");
    $db_sql = file_get_contents(dirname(__FILE__).'/../database.sql');
    if (!$db->inTransaction()) {
        $db->beginTransaction();
    }
    exec_sql_script($db, $db_sql);
    log_info("schema upgrade successful");
}
else if ($schema_version > SCHEMA_VERSION) {
    $msg = "database schema version $schema_version is greater than expected version ".SCHEMA_VERSION;
    log_error($msg);
    throw new UnexpectedValueException($msg);
}
else {
    log_debug("database schema is up to date (version $schema_version)");
}

if ($db->inTransaction()) {
    $db->commit();
    $db->exec("VACUUM");
}
