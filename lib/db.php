<?php
require_once dirname(__FILE__) . '/../init.php';
require_once dirname(__FILE__) . '/sql_parse.php';

$db = new PDO('sqlite:' . dirname(__FILE__) . '/../data/database.sqlite3');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA foreign_keys = ON;');

define("SCHEMA_VERSION", 6);

/**
 * @param PDO $db opened database connection
 * @param string $sql SQL script content (multiple statements delimited by ';'
 */
function exec_sql_script($db, $sql)
{
    $sql = remove_comments($sql);
    $sql = remove_remarks($sql);
    $stmts = split_sql_file(trim($sql), ';');
    foreach ($stmts as $stmt) {
        $db->exec($stmt);
    }
}

/**
 * Drop all user tables and indexes from an sqlite3 database.
 * @param PDO $db target database
 * @param string[] $excluded_names table or index names to exclude
 */
function truncate_database($db, $excluded_names)
{
    $excluded_names[] = 'sqlite_sequence';
    $excluded_names[] = 'sqlite_master';
    $excluded_names = array_unique($excluded_names);

    $db->exec("PRAGMA writable_schema = 1;");
    $db->exec("DELETE FROM sqlite_master WHERE type = 'table' AND name NOT IN ('" . join("', '", $excluded_names) . "');");
    $db->exec("DELETE FROM sqlite_master WHERE type = 'index';");
    $db->exec("DELETE FROM sqlite_master WHERE type = 'trigger';");
    $db->exec("PRAGMA writable_schema = 0;");

    $in_transaction = $db->inTransaction();
    if ($in_transaction) {
        $db->commit();
    }
    $db->exec("VACUUM");
    if ($in_transaction) {
        $db->beginTransaction();
    }
}

$schema_version = 0;
try {
    $schema = $db->query("SELECT MAX(version) FROM schema");
    $schema_version = $schema->fetchColumn(0);
    $schema->closeCursor();
} catch (PDOException $e) {
    log_warning("failed to read schema version from database, recreating");
    $schema_sql = file_get_contents(dirname(__FILE__) . '/../schema.sql');
    $db->beginTransaction();
    exec_sql_script($db, $schema_sql);
}

if ($schema_version < SCHEMA_VERSION) {
    log_info("schema version $schema_version is older than " . SCHEMA_VERSION . ". upgrading...");
    $db_sql = file_get_contents(dirname(__FILE__) . '/../database.sql');
    $inserts_sql = file_get_contents(dirname(__FILE__) . '/../inserts.sql');
    if (!$db->inTransaction()) {
        $db->beginTransaction();
    }
    truncate_database($db, ['schema']);
    exec_sql_script($db, $db_sql);
    exec_sql_script($db, $inserts_sql);
    $db->exec("INSERT INTO `schema`(`version`) VALUES (" . SCHEMA_VERSION . ");");
    log_info("schema upgrade successful");
} else if ($schema_version > SCHEMA_VERSION) {
    $msg = "database schema version $schema_version is greater than expected version " . SCHEMA_VERSION;
    log_error($msg);
    throw new UnexpectedValueException($msg);
}

if ($db->inTransaction()) {
    $db->commit();
    $db->exec("VACUUM");
}
