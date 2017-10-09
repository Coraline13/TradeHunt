<?php
require_once dirname(__FILE__) . '/../init.php';

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
);

$supported_locales = null;

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
    global $string_code_to_name, $supported_locales;
    static $strings = null;


    if (!$strings || !$supported_locales) {
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
        $string_data = json_decode(file_get_contents(dirname(__FILE__) . '/../strings.json'), true);
        $supported_locales = $string_data['supported_locales'];
        $strings = $string_data['strings'];
        if (!$supported_locales || count($supported_locales) == 0) {
            throw new UnexpectedValueException("strings.json must define at least one locale");
        }

        foreach ($string_code_to_name as $code => $string_name) {
            unset($defined_codes[$code]);
            if (!isset($strings[$string_name])) {
                throw new UnexpectedValueException("$string_name is missing from strings.json");
            } else {
                if (preg_match('/[^a-z0-9_]/', $string_name)) {
                    throw new UnexpectedValueException("string name can only contain [a-z0-9_] ($string_name)");
                }
                foreach ($supported_locales as $locale) {
                    if (!isset($strings[$string_name][$locale])) {
                        throw new UnexpectedValueException("$string_name is missing '$locale' translation in strings.json");
                    }
                }
            }
        }

        // check that all string codes are defined in $string_code_to_name
        if (count($defined_codes) != 0) {
            throw new UnexpectedValueException('missing $string_code_to_name definition for codes ' . json_encode($defined_codes));
        }
    }


    return $strings;
}

_load_strings();

/**
 * Return the translated version of the given string according to cookie-determined locale.
 * @param $string_code int STRING_ enumeration constant
 * @return string translated string
 * @throws InvalidArgumentException in case of bad string code or locale
 */
function get_string($string_code)
{
    global $string_code_to_name, $supported_locales;
    $strings = _load_strings();

    $string_name = isset($string_code_to_name[$string_code]) ? $string_code_to_name[$string_code] : null;
    if (!$string_name) {
        $string_name = is_string($string_code) ? $string_code : null;
    }
    if (!$string_name) {
        throw new InvalidArgumentException("invalid or unknown string code $string_code");
    }

    $locale = $supported_locales[0];
    if (isset($_COOKIE) && isset($_COOKIE['locale'])) {
        $locale = $_COOKIE['locale'];
        if (!in_array($locale, $supported_locales)) {
            throw new InvalidArgumentException("invalid or unknown locale '$string_code'");
        }
    }

    return replace_strings($strings[$string_name][$locale]);
}

/**
 * Replace tokens of the form {{ string_name }} with their get_string value.
 *
 * Example: "'%1$s' is not a valid {{ email_address }}" => "'%1$s' is not a valid e-mail address",
 * when there exists a string "email_address": { "en": "e-mail address" },
 * @param string $str string with unreplaced tokens
 * @return string string with tokens replaced
 */
function replace_strings($str) {
    return preg_replace_callback('/\{\{\s*([a-z0-9_]+)\s*\}\}/', function ($matches) {
        return get_string($matches[1]);
    }, $str);
}

/**
 * Get a parameterized string translated according to cookie locale.
 * @param int $string_code STRING_ constant referring to a string that contains printf format specifiers (%s, %d etc)
 * @param mixed $fmt_args,... arguments for format specifiers
 * @return string translated and formatted string
 * @see get_string()
 */
function get_string_format($string_code, $fmt_args) {
    $fmt = get_string($string_code);
    $args = func_get_args();
    array_shift($args);
    return vsprintf($fmt, $args);
}
