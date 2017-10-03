<?php
require_once dirname(__FILE__).'/lib/log.php';

/**
 * Format the stack trace of an exception as a multi-line string.
 * This is necessary because Exception->getTraceAsString truncated its output...
 * @param Throwable $exception target exception
 * @return string formatted stack trace
 */
function format_exception_trace($exception) {
    $rtn = "";
    $count = 0;
    foreach ($exception->getTrace() as $frame) {
        $args = "";
        if (isset($frame['args'])) {
            $args = array();
            foreach ($frame['args'] as $arg) {
                if (is_string($arg)) {
                    $args[] = "'" . $arg . "'";
                } elseif (is_array($arg)) {
                    $args[] = "Array";
                } elseif (is_null($arg)) {
                    $args[] = 'NULL';
                } elseif (is_bool($arg)) {
                    $args[] = ($arg) ? "true" : "false";
                } elseif (is_object($arg)) {
                    $args[] = get_class($arg);
                } elseif (is_resource($arg)) {
                    $args[] = get_resource_type($arg);
                } else {
                    $args[] = $arg;
                }
            }
            $args = join(", ", $args);
        }
        $rtn .= sprintf( "\t#%s %s(%s): %s(%s)\n",
            $count,
            isset($frame['file']) ? $frame['file'] : 'unknown file',
            isset($frame['line']) ? $frame['line'] : 'unknown line',
            (isset($frame['class']))  ? $frame['class'].$frame['type'].$frame['function'] : $frame['function'],
            $args );
        $count++;
    }
    return "\t".trim($rtn);
}

/**
 * Write an appropriate JSON response & HTTP status code for the given exception.
 * @param Throwable $exc
 */
function write_error_response_json($exc) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $error_response = [
        "error_code" => $exc->getCode(),
        "message" => $exc->getMessage(),
        "ip" => $ip,
    ];

    if ($ip === "127.0.0.1" || substr($ip, 0, strlen("10.17.0.")) === "10.17.0.") {
        $error_response['trace'] = $exc->getTraceAsString();
        $cause = $exc->getPrevious();
        $outer = &$error_response;
        while ($cause !== null) {
            $outer['cause'] = [
                "trace" => $cause->getTraceAsString(),
                "message" => $cause->getMessage()
            ];
            if ($cause instanceof PDOException) {
                $outer['cause']['errorInfo'] = $cause->errorInfo;
            }
            $outer = &$outer['cause'];
            $cause = $cause->getPrevious();
        }
    }

    http_response_code($exc instanceof APIException ? $exc->getRecommendedHttpStatus() : 500);
    header("Content-Type: application/json");
    echo json_encode($error_response);
}

/**
 * Handler function for set_exception handler.
 * @param Throwable $exc exception that was not caught by the program
 * @see set_exception_handler()
 */
function exception_handler($exc) {
    log_error("unhandled exception of type ".get_class($exc).": ".$exc->getMessage()."\n".format_exception_trace($exc));
    $cause = $exc->getPrevious();
    while ($cause !== null) {
        log_error("previous exception was caused by ".get_class($cause).": ".$cause->getMessage()."\n".format_exception_trace($cause));
        $cause = $cause->getPrevious();
    }

    write_error_response_json($exc);
}

/**
 * Handler function for set_error_handler
 * @param int $errno the level of the error raised
 * @param string $errstr the error message
 * @param string $errfile the filename that the error was raised in
 * @param int $errline the line number the error was raised at
 * @param array $errcontext [DEPRECATED] an array that points to the active symbol table at the point the error occurred
 * @return bool returns false if normal error handling should resume, true if the error was handled and the program can halt
 * @throws ErrorException this implementation converts all errors into ErrorException, to be handled by exception_handler
 * @see set_error_handler()
 * @see exception_handler()
 */
function error_handler($errno , $errstr, $errfile = null, $errline = -1, $errcontext = null) {
    $msg = "PHP error $errno - '$errstr'";
    if ($errfile) {
        $msg .= " in file $errfile";
    }
    if ($errline >= 0) {
        $msg .= " at line $errline";
    }
    log_error($msg);
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

set_exception_handler('exception_handler');
set_error_handler('error_handler', E_ALL | E_STRICT);
