<?php
require_once dirname(__FILE__).'/../init.php';
require_once dirname(__FILE__).'/db.php';
require_once dirname(__FILE__).'/strings.php';
require_once dirname(__FILE__).'/validators.php';


define("ERROR_USERNAME_EXISTS", 401);
define("ERROR_EMAIL_EXISTS", 402);
define("ERROR_USER_UNKNOWN", 403);
define("ERROR_GENERIC_API", 404);
define("ERROR_METHOD_NOT_ALLOWED", 405);
define("ERROR_GENERIC_VALIDATION", 406);
define("ERROR_WRONG_PASSWORD", 407);
define("ERROR_USER_NOT_FOUND", 408);

$error_info = array(
    ERROR_USERNAME_EXISTS => [STRING_USERNAME_EXISTS, 409],
    ERROR_EMAIL_EXISTS => [STRING_EMAIL_EXISTS, 409],
    ERROR_USER_UNKNOWN => [STRING_REGISTER_UNKNOWN_ERR, 500],
    ERROR_GENERIC_API => [STRING_GENERIC_ERROR, 500],
    ERROR_METHOD_NOT_ALLOWED => [STRING_NO_STRING, 405],
    ERROR_GENERIC_VALIDATION => [STRING_VALIDATION_ERROR, 400],
    ERROR_WRONG_PASSWORD => [STRING_WRONG_PASSWORD, 403],
    ERROR_USER_NOT_FOUND => [STRING_WRONG_PASSWORD, 403],
);

class APIException extends Exception
{
    private $recommended_http_status;

    /**
     * Exception for encapsulating application errors.
     * @param int $error_code ERROR_ enum value
     * @param string $message optional message to add to the exception; if omitted, the exception must have a string associated
     * @param Throwable $cause underlying exception
     */
    public function __construct($error_code, $cause, $message = null)
    {
        global $error_info;
        $this->recommended_http_status = $error_info[$error_code][1];
        parent::__construct($message ? $message : get_string($error_info[$error_code][0]), $error_code, $cause);
    }

    /**
     * @return int HTTP status code associated with the erorr
     */
    public function getRecommendedHttpStatus()
    {
        return $this->recommended_http_status;
    }
}

/**
 * Check that the request method is one of $request_method, or throw an exception
 * @param string[] $allowed_methods methods allowed for the request
 * @throws APIException if the request method is not in $allowed_methods
 */
function check_method($allowed_methods)
{
    $method = $_SERVER['REQUEST_METHOD'];
    $allowed_methods[] = "HEAD";
    if (!in_array($method, $allowed_methods)) {
        throw new APIException(ERROR_METHOD_NOT_ALLOWED, null,
            "$method not allowed, expected one of ".json_encode($allowed_methods));
    }
}

class UserException extends APIException
{
}

class ValidationException extends APIException
{
    private $arg_name;
    private $validation_error;

    /**
     * ValidationException constructor.
     * @param string $arg_name name of the parameter that was invalid
     * @param string $validation_error description of the validation error
     * @param Throwable|null $cause exception that caused the error, if any
     */
    public function __construct($arg_name, $validation_error, $cause = null)
    {
        parent::__construct(ERROR_GENERIC_VALIDATION, $cause, get_string(STRING_VALIDATION_ERROR));
        $this->arg_name = $arg_name;
        $this->validation_error = $validation_error;
    }

    /**
     * @return string name of the parameter that was invalid
     */
    public function getArgName()
    {
        return $this->arg_name;
    }

    /**
     * @return string description of the validation error
     */
    public function getValidationError()
    {
        return $this->validation_error;
    }
}

/**
 * Throw an exception if the given variable is null
 * @param mixed $obj target object
 * @param string $arg_name argument name used for error formatting
 * @return mixed $obj unmodified
 * @throws ValidationException if $obj is_null()
 * @see is_null()
 * @see require_non_empty(), require_array_value()
 */
function require_non_null($obj, $arg_name)
{
    if (is_null($obj)) {
        throw new ValidationException($arg_name, get_string(STRING_PARAMETER_REQUIRED));
    }
    return $obj;
}

/**
 * Throw an exception if the given object is empty or null
 * @param mixed $obj target object
 * @param string $arg_name argument name used for error formatting
 * @return mixed $obj unmodified
 * @throws ValidationException if $obj is empty()
 * @see empty()
 * @see require_non_null(), require_array_value()
 */
function require_non_empty($obj, $arg_name)
{
    if (is_null($obj)) {
        throw new ValidationException($arg_name, get_string(STRING_PARAMETER_REQUIRED));
    }
    return $obj;
}

/**
 * Get a value from the given array and throw an exception if it does not exist
 * @param array $arr target array
 * @param string $key key name in the array
 * @param bool $allow_empty false to also check that the value is not empty
 * @return mixed $arr[$key]
 * @throws ValidationException if $key does not exist in $arr
 * @see array_key_exists(), empty()
 * @see require_non_null(), require_non_empty()
 */
function require_array_value($arr, $key, $allow_empty = true)
{
    if (!array_key_exists($key, $arr) || ($allow_empty ? is_null($arr[$key]) : empty($arr[$key]))) {
        throw new ValidationException($key, get_string(STRING_PARAMETER_REQUIRED));
    }
    return $arr[$key];
}

/**
 * Fetch exactly one row from a query, or throw an exception. The fetch style is PDO::FETCH_ASSOC.
 * @param PDOStatement $stmt target query, must have been execute()d
 * @param string $table_name
 * @param string $key_name
 * @param mixed $key_val
 * @param int $string_code error format STRING
 * @return array successful return value of $stmt->fetch(PDO::FETCH_ASSOC)
 * @throws ValidationException if there was not exactly one row to fetch
 * @internal param string $field field name for error formatting
 * @see PDOStatement::fetch()
 */
function require_fetch_one($stmt, $table_name, $key_name, $key_val, $string_code = STRING_VALIDATE_FETCH_ONE)
{
    $result = null;
    $cause = null;
    try {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $cause = $e;
        $result = null;
    }

    $remaining = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($result) || !empty($remaining)) {
        throw new ValidationException($table_name, get_string_format($string_code, $table_name, $key_name, $key_val), $cause);
    }
    return $result;
}

/**
 * Validate a value against a given validator
 * @param mixed $obj object to be validated
 * @param string $arg_name argument name used for error formatting
 * @param callable[] $validators array of validators [(mixed) -> string|null], that are evaluated in the order given
 * @return mixed $obj unmodified
 * @throws ValidationException if $validator returns an error
 */
function validate_value($obj, $arg_name, $validators)
{
    foreach ($validators as $validator) {
        $validation_error = call_user_func($validator, $obj);
        if (!empty($validation_error)) {
            throw new ValidationException($arg_name, $validation_error);
        }
    }
    return $obj;
}

/**
 * Validate a value from an array against a given validator.
 * @param array $arr target array
 * @param string $key key name in the array
 * @param callable[] $validators array of validators [(mixed) -> string|null], that are evaluated in the order given
 * @return mixed $arr[$key]
 * @throws ValidationException if $validator returns an error
 */
function validate_array_value($arr, $key, $validators)
{
    $obj = array_key_exists($key, $arr) ? $arr[$key] : null;
    foreach ($validators as $validator) {
        $validation_error = call_user_func($validator, $obj);
        if (!empty($validation_error)) {
            throw new ValidationException($key, $validation_error);
        }
    }
    return $obj;
}

require_once dirname(__FILE__).'/models/Location.php';
require_once dirname(__FILE__).'/models/Profile.php';
require_once dirname(__FILE__).'/models/User.php';
require_once dirname(__FILE__).'/models/Session.php';
require_once dirname(__FILE__).'/models/Tag.php';
require_once dirname(__FILE__).'/models/Listing.php';
require_once dirname(__FILE__).'/models/Image.php';
require_once dirname(__FILE__).'/models/Bookmark.php';
require_once dirname(__FILE__).'/models/Trade.php';

/** @var User|null $_USER */
$_USER = null;

try {
    $token = isset($_COOKIE[CFG_COOKIE_AUTH]) ? $_COOKIE[CFG_COOKIE_AUTH] : null;
    $session = Session::getByToken($token);
    $_USER = $session != null ? $session->getUser() : null;
} catch (APIException $e) {
    log_warning("Session validation failed because of unexpected error");
    log_exception($e, LOG_LEVEL_WARNING);
    $_USER = null;
}

/**
 * Check the current authentication state, and redirect the user if necessary.
 *
 * If $desired_state is true, a non-authenticated user will be redirected to the login page.
 *
 * If $desired_state is false, an authenticated user will be redirected to his profile page.
 * @param bool $desired_state desired authentication state
 */
function force_authentication($desired_state = true)
{
    global $_USER;
    if (empty($_USER) && $desired_state) {
        log_debug("Not authenticated, redirecting to login");
        header("Location: ${GLOBALS['root']}login.php", true, 302);
        exit();
    }
    else if (!empty($_USER) && !$desired_state) {
        log_debug("Already authenticated, redirecting to profile");
        header("Location: ${GLOBALS['root']}profile.php", true, 302);
        exit();
    }
}
