<?php
$log = fopen(dirname(__FILE__).'/../application.log', 'at');

/**
 * @return string current date and time formatted as a string
 */
function format_timestamp() {
    date_default_timezone_set("Europe/Paris");
    $now = new DateTime();
    return $now->format("Y/m/d H:i:s P");
}

define("LOG_LEVEL_DEBUG", "DEBUG");
define("LOG_LEVEL_INFO", "INFO");
define("LOG_LEVEL_WARNING", "WARNING");
define("LOG_LEVEL_ERROR", "ERROR");

/**
 * Possible values for the $level parameter of _log_write
 * @see _log_write()
 */
$log_levels = array(LOG_LEVEL_DEBUG, LOG_LEVEL_INFO, LOG_LEVEL_WARNING, LOG_LEVEL_ERROR);

/**
 * Write a message to the application log
 * @param $message string log message
 * @param $level string log level, one of $log_levels
 * @see $log_levels
 */
function _log_write($message, $level) {
    global $log, $log_levels;
    assert(in_array($level, $log_levels), sprintf("%s is not a valid log level", $level));
    fprintf($log, "[%s][%s] %s\n", format_timestamp(), $level, $message);
}

/**
 * Write a message to the application log
 * @param $message string log message
 */
function log_debug($message) {
    _log_write($message, LOG_LEVEL_DEBUG);
}

/**
 * Write a message to the application log
 * @param $message string log message
 */
function log_info($message) {
    _log_write($message, LOG_LEVEL_INFO);
}

/**
 * Write a message to the application log
 * @param $message string log message
 */
function log_warning($message) {
    _log_write($message, LOG_LEVEL_WARNING);
}

/**
 * Write a message to the application log
 * @param $message string log message
 */
function log_error($message) {
    _log_write($message, LOG_LEVEL_ERROR);
}
