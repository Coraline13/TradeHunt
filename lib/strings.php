<?php
require_once dirname(__FILE__).'/../init.php';

define("STRING_NO_STRING", 0);
define("STRING_USERNAME_EXISTS", 1);
define("STRING_EMAIL_EXISTS", 2);
define("STRING_REGISTER_UNKNOWN_ERR", 3);
define("STRING_GENERIC_ERROR", 4);
define("STRING_VALIDATION_ERROR", 5);
define("STRING_PARAMETER_REQUIRED", 6);
define("STRING_VALIDATE_LENGTH", 7);
define("STRING_VALIDATE_REGEX", 8);
define("STRING_VALIDATE_CHARSET", 9);
define("STRING_VALIDATE_RANGE", 10);
define("STRING_VALIDATE_EMAIL", 11);
define("STRING_VALIDATE_PHONE", 12);
define("STRING_EMAIL_ADDRESS", 13);
define("STRING_PHONE_NUMBER", 14);
define("STRING_VALIDATE_FETCH_ONE", 15);
define("STRING_FIRST_NAME", 16);
define("STRING_LAST_NAME", 17);
define("STRING_LOCATION", 18);
define("STRING_USERNAME", 19);
define("STRING_PASSWORD", 20);
define("STRING_LOG_IN", 21);
define("STRING_REGISTER", 22);
define("STRING_PERSONAL_INFO", 23);
define("STRING_CHOOSE_OPTION", 24);
define("STRING_REPEAT_PASSWORD", 25);
define("STRING_PASSWORD_MISMATCH", 26);
define("STRING_WRONG_PASSWORD", 27);
define("STRING_USER_NOT_FOUND", 28);
define("STRING_USERNAME_OR_EMAIL", 29);
define("STRING_USER_PROFILE", 30);
define("STRING_LOGIN_TO_REGISTER", 31);
define("STRING_REGISTER_TO_LOGIN", 32);
define("STRING_SELECT_LANGUAGE", 33);
define("STRING_IDENTIFIER", 34);
define("STRING_LANG_FR", 35);
define("STRING_LANG_RO", 36);
define("STRING_LANG_EN", 37);
define("STRING_LOGOUT", 38);
define("STRING_LOGIN_INFO", 39);
define("STRING_APP_NAME", 40);

/**
 * Array mapping STRING constants to a code name that is used to specify localizations in strings.json.
 *
 * All STRING constants must have an entry in this array, else _load_strings() will fail.
 * @see _load_strings()
 */
$string_code_to_name = array(
    STRING_NO_STRING => "no_string",

    STRING_GENERIC_ERROR => "generic_error",
    STRING_PARAMETER_REQUIRED => "parameter_required",

    STRING_USERNAME_EXISTS => "username_exists",
    STRING_EMAIL_EXISTS => "email_exists",
    STRING_REGISTER_UNKNOWN_ERR => "register_unknown_err",

    STRING_VALIDATION_ERROR => "validation_error",
    STRING_VALIDATE_LENGTH => "validate_length",
    STRING_VALIDATE_REGEX => "validate_regex",
    STRING_VALIDATE_CHARSET => "validate_charset",
    STRING_VALIDATE_RANGE => "validate_range",
    STRING_VALIDATE_EMAIL => "validate_email",
    STRING_VALIDATE_PHONE => "validate_phone",
    STRING_VALIDATE_FETCH_ONE => "fetch_error",

    STRING_USERNAME => "username",
    STRING_PASSWORD => "password",
    STRING_EMAIL_ADDRESS => "email_address",
    STRING_FIRST_NAME => "first_name",
    STRING_LAST_NAME => "last_name",
    STRING_PHONE_NUMBER => "phone_number",
    STRING_LOCATION => "location",
    STRING_LOG_IN => "log_in",
    STRING_REGISTER => "register",
    STRING_PERSONAL_INFO => "personal_info",
    STRING_CHOOSE_OPTION => "choose_option",
    STRING_REPEAT_PASSWORD => "repeat_password",
    STRING_PASSWORD_MISMATCH => "password_mismatch",
    STRING_WRONG_PASSWORD => "wrong_password",
    STRING_USER_NOT_FOUND => "user_not_found",
    STRING_USERNAME_OR_EMAIL => "username_or_email",
    STRING_USER_PROFILE => "user_profile",
    STRING_LOGIN_TO_REGISTER => "login_to_register",
    STRING_REGISTER_TO_LOGIN => "register_to_login",
    STRING_SELECT_LANGUAGE => "select_language",
    STRING_IDENTIFIER => "identifier",
    STRING_LANG_FR => "lang_fr",
    STRING_LANG_RO => "lang_ro",
    STRING_LANG_EN => "lang_en",
    STRING_LOGOUT => "logout",
    STRING_LOGIN_INFO => "login_info",
    STRING_APP_NAME => "app_name",
);

$_SUPPORTED_LOCALES = null;
$_LOCALE = null;

function file_get_contents_utf8($fn)
{
    $content = file_get_contents($fn);
    return mb_convert_encoding($content, 'UTF-8',
        mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}

/**
 * Load and statically cache the strings.json file describing localized strings for all STRING_ constants.
 *
 * May throw validation errors on startup if string definitions are missing.
 * @return string[] localized strings
 * @throws UnexpectedValueException STRING constant not declard in $string_code_to_name,
 *   or string is missing from strings.json
 * @see $string_code_to_name
 */
function _load_strings()
{
    global $string_code_to_name, $_SUPPORTED_LOCALES, $_LOCALE;
    static $strings = null;

    if (!$strings || !$_SUPPORTED_LOCALES) {
        // check that all STRING_ constants have a distinct value
        $defined_codes = array();
        foreach (get_defined_constants() as $name => $val) {
            if (strncmp($name, "STRING_", strlen("STRING_")) == 0) {
                if (isset($defined_codes[$val])) {
                    throw new UnexpectedValueException("duplicate string code value $val for $name");
                }
                $defined_codes[$val] = $name;
            }
        }

        // read string data from JSON
        $string_data = json_decode(file_get_contents_utf8(dirname(__FILE__).'/../strings.json'), true, 512, JSON_UNESCAPED_UNICODE);
        $_SUPPORTED_LOCALES = $string_data['supported_locales'];
        $strings = $string_data['strings'];
        if (!$_SUPPORTED_LOCALES || count($_SUPPORTED_LOCALES) == 0) {
            throw new UnexpectedValueException("strings.json must define at least one locale");
        }

        $_LOCALE = $_SUPPORTED_LOCALES[0];
        if (isset($_COOKIE) && isset($_COOKIE['locale'])) {
            $_LOCALE = $_COOKIE[CFG_COOKIE_LOCALE];
            if (!in_array($_LOCALE, $_SUPPORTED_LOCALES)) {
                throw new InvalidArgumentException("invalid or unknown locale '$_LOCALE'");
            }
        } else {
            setcookie(CFG_COOKIE_LOCALE, $_LOCALE, time() + 60 * 60 * 24 * 365 /*1 year*/, "/");
        }

        setlocale(LC_CTYPE | LC_COLLATE, $_LOCALE);

        foreach ($string_code_to_name as $code => $string_name) {
            unset($defined_codes[$code]);
            if (!isset($strings[$string_name])) {
                throw new UnexpectedValueException("$string_name is missing from strings.json");
            } else {
                if (preg_match('/[^a-z0-9_]/', $string_name)) {
                    throw new UnexpectedValueException("string name can only contain [a-z0-9_] ($string_name)");
                }
                foreach ($_SUPPORTED_LOCALES as $locale) {
                    if (!isset($strings[$string_name][$locale])) {
                        throw new UnexpectedValueException("$string_name is missing '$locale' translation in strings.json");
                    }
                }
            }
        }

        // check that all string codes are defined in $string_code_to_name
        if (count($defined_codes) != 0) {
            throw new UnexpectedValueException('missing $string_code_to_name definition for codes '.json_encode($defined_codes));
        }
    }

    return $strings;
}

_load_strings();

/**
 * Return the translated version of the given string according to cookie-determined locale.
 * @param int $string_code STRING_ enumeration constant
 * @return string translated string
 * @throws InvalidArgumentException in case of bad string code or locale
 * @see _t()
 */
function get_string($string_code)
{
    global $string_code_to_name, $_LOCALE;
    $strings = _load_strings();

    $string_name = isset($string_code_to_name[$string_code]) ? $string_code_to_name[$string_code] : null;
    if (!$string_name) {
        $string_name = is_string($string_code) ? $string_code : null;
    }
    if (!$string_name) {
        throw new InvalidArgumentException("invalid or unknown string code $string_code");
    }

    return replace_strings($strings[$string_name][$_LOCALE]);
}

/**
 * Replace tokens of the form {{ string_name }} with their get_string value.
 *
 * Example: "'%1$s' is not a valid {{ email_address }}" => "'%1$s' is not a valid e-mail address",
 * when there exists a string "email_address": { "en": "e-mail address" },
 * @param string $str string with unreplaced tokens
 * @return string string with tokens replaced
 */
function replace_strings($str)
{
    return preg_replace_callback('/\{\{\s*([a-z0-9_]+)\s*(?:\|([uclt])\s*)?\}\}/', function ($matches) {
        $case = null;
        if (isset($matches[2]) && !empty($matches[2])) {
            $case = $matches[2];
        }
        return _t($case, $matches[1]);
    }, $str);
}

/**
 * Get a parameterized string translated according to cookie locale.
 * @param int $string_code STRING_ constant referring to a string that contains printf format specifiers (%s, %d etc)
 * @param mixed $fmt_args,... arguments for format specifiers
 * @return string translated and formatted string
 * @see get_string()
 */
function get_string_format($string_code, $fmt_args = null)
{
    $fmt = get_string($string_code);
    $args = func_get_args();
    array_shift($args);
    return vsprintf($fmt, $args);
}

/**
 * Get a parameterized string translated according to cookie locale.
 * @param string $case optional case conversion; pass 'u' for Upper case, 'l' for lower case or 't' for Title Case,
 *  'c' for CAPS, null for no conversion
 * @param int $string_code parameter to get_string
 * @param mixed $fmt_args,... arguments for format specifiers
 * @return string translated and formatted string with optional case conversion
 * @see get_string()
 */
function _t($case, $string_code, $fmt_args = null)
{
    $str = get_string($string_code);
    switch ($case) {
        case 'u':
            $str = mb_strtoupper(mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8').mb_substr($str, 1, null,'UTF-8');
            break;
        case 't':
            $str = mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
            break;
        case 'c':
            $str = mb_strtoupper($str, 'UTF-8');
            break;
        case 'l':
            $str = mb_strtolower($str, 'UTF-8');
            break;
    }

    $args = func_get_args();
    array_shift($args); // $case
    array_shift($args); // $string_code
    return vsprintf($str, $args);
}
