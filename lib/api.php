<?php
require_once dirname(__FILE__).'/db.php';
require_once dirname(__FILE__).'/log.php';
require_once dirname(__FILE__).'/strings.php';


define("ERROR_USERNAME_EXISTS", 401);
define("ERROR_EMAIL_EXISTS", 402);
define("ERROR_USER_UNKNOWN", 403);

$error_code_to_string_code = array(
    ERROR_USERNAME_EXISTS => STRING_USERNAME_EXISTS,
    ERROR_EMAIL_EXISTS => STRING_EMAIL_EXISTS,
    ERROR_USER_UNKNOWN => STRING_REGISTER_UNKNOWN_ERR,
);

class APIException extends Exception {

    /**
     * Exception for encapsulating application errors.
     * @param int $error_code ERROR_ enum value
     * @param Throwable $cause underlying exception
     */
    public function __construct($error_code, $cause)
    {
        global $error_code_to_string_code;
        parent::__construct(get_string($error_code_to_string_code[$error_code]), $error_code, $cause);
    }
}

require_once dirname(__FILE__).'/models/User.php';
