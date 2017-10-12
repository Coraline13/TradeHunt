<?php
require_once dirname(__FILE__).'/config.php';
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
 * Check if a given IP lies in a reserved private or loopback IP range
 *  (192.168.0.0/16, 172.16.0.0/12, 10.0.0.0/8) (127.0.0.0/8)
 * @param string $ip ip in string form
 * @return bool true if the ip is private
 */
function ip_is_private ($ip) {
    $pri_addrs = array (
        '10.0.0.0|10.255.255.255', // single class A network
        '172.16.0.0|172.31.255.255', // 16 contiguous class B network
        '192.168.0.0|192.168.255.255', // 256 contiguous class C network
        '127.0.0.0|127.255.255.255' // loopback
    );

    $long_ip = ip2long ($ip);
    if ($long_ip != -1) {

        foreach ($pri_addrs AS $pri_addr) {
            list ($start, $end) = explode('|', $pri_addr);

            if ($long_ip >= ip2long ($start) && $long_ip <= ip2long ($end)) {
                return true;
            }
        }
    }

    return false;
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

    if ($exc instanceof ValidationException) {
        $error_response['validation_errors'] = [
            $exc->getArgName() => $exc->getValidationError()
        ];
    }

    if (ip_is_private($ip)) {
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
    log_exception($exc, LOG_LEVEL_ERROR);
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
    $exc = new ErrorException($errstr, 0, $errno, $errfile, $errline);
    if (PHP_VERSION_ID < 50524) {
        // before PHP 5.5, exceptions thrown from the error handler were not caught by the exception handler
        exception_handler($exc);
        die();
    }
    throw $exc;
}

set_exception_handler('exception_handler');
set_error_handler('error_handler', E_ALL | E_STRICT);
error_reporting(E_ALL);

$GLOBALS['secure'] = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    $GLOBALS['secure'] = true;
}
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $GLOBALS['secure'] = true;
}

function parse_root_url($request_url) {
    $url = parse_url($request_url);
    $path = isset($url['path']) ? $url['path'] : '/';
    $path_components = preg_split('|/|', $path, -1, PREG_SPLIT_NO_EMPTY);
    $public_root_index = array_search('public', $path_components);
    if ($public_root_index !== false) {
        $path = '/'.implode('/', array_slice($path_components, 0, $public_root_index + 1, true)).'/';
    }
    else {
        $path = '/';
    }

    $scheme = $GLOBALS['secure'] ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return "$scheme://$host$path";
}
$GLOBALS['root'] = parse_root_url($_SERVER['REQUEST_URI']);
