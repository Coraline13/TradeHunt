<?php
require_once dirname(__FILE__).'/db.php';
require_once dirname(__FILE__).'/log.php';
require_once dirname(__FILE__).'/strings.php';


define("ERROR_USERNAME_EXISTS", 401);
define("ERROR_EMAIL_EXISTS", 402);
define("ERROR_USER_UNKNOWN", 403);

$error_info = array(
    ERROR_USERNAME_EXISTS => [STRING_USERNAME_EXISTS, 409],
    ERROR_EMAIL_EXISTS => [STRING_EMAIL_EXISTS, 409],
    ERROR_USER_UNKNOWN => [STRING_REGISTER_UNKNOWN_ERR, 500],
);

class APIException extends Exception {

    private $recommended_http_status;

    /**
     * Exception for encapsulating application errors.
     * @param int $error_code ERROR_ enum value
     * @param Throwable $cause underlying exception
     */
    public function __construct($error_code, $cause)
    {
        global $error_info;
        $this->recommended_http_status = $error_info[$error_code][1];
        parent::__construct(get_string($error_info[$error_code][0]), $error_code, $cause);
    }

    /**
     * @return int HTTP status code associated with the erorr
     */
    public function getRecommendedHttpStatus()
    {
        return $this->recommended_http_status;
    }
}

require_once dirname(__FILE__).'/models/User.php';
