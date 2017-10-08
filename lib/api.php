<?php
require_once dirname(__FILE__) . '/../init.php';
require_once dirname(__FILE__) . '/db.php';
require_once dirname(__FILE__) . '/strings.php';


define("ERROR_USERNAME_EXISTS", 401);
define("ERROR_EMAIL_EXISTS", 402);
define("ERROR_USER_UNKNOWN", 403);
define("ERROR_GENERIC_API", 404);
define("ERROR_METHOD_NOT_ALLOWED", 405);
define("ERROR_GENERIC_VALIDATION", 406);

$error_info = array(
    ERROR_USERNAME_EXISTS => [STRING_USERNAME_EXISTS, 409],
    ERROR_EMAIL_EXISTS => [STRING_EMAIL_EXISTS, 409],
    ERROR_USER_UNKNOWN => [STRING_REGISTER_UNKNOWN_ERR, 500],
    ERROR_GENERIC_API => [STRING_GENERIC_ERROR, 500],
    ERROR_METHOD_NOT_ALLOWED => [STRING_NO_STRING, 405],
    ERROR_GENERIC_VALIDATION => [STRING_VALIDATION_ERROR, 400]
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
 * Exception for encapsulating application errors.
 * @param string[] $allowed_methods methods allowed for the request
 * @throws APIException if the request method is not in $allowed_methods
 */
function check_method($allowed_methods)
{
    $method = $_SERVER['REQUEST_METHOD'];
    $allowed_methods[] = "HEAD";
    if (!in_array($method, $allowed_methods)) {
        throw new APIException(ERROR_METHOD_NOT_ALLOWED, null,
            "$method not allowed, expected one of " . json_encode($allowed_methods));
    }
}

class UserException extends APIException { }

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

function require_non_null($obj, $arg_name) {
    if (is_null($obj)) {
        throw new ValidationException($arg_name, get_string(STRING_PARAMETER_REQUIRED));
    }
    return $obj;
}

function require_array($arr, $key) {
    if (array_key_exists($key, $arr)) {
        throw new ValidationException($key, get_string(STRING_PARAMETER_REQUIRED));
    }
    return $arr[$key];
}

require_once dirname(__FILE__) . '/models/Location.php';
require_once dirname(__FILE__) . '/models/Profile.php';
require_once dirname(__FILE__) . '/models/User.php';
require_once dirname(__FILE__) . '/models/Session.php';
