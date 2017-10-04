<?php
require_once dirname(__FILE__).'/../init.php';

define("STRING_NO_STRING", 0);
define("STRING_USERNAME_EXISTS", 1);
define("STRING_EMAIL_EXISTS", 2);
define("STRING_REGISTER_UNKNOWN_ERR", 3);
define("STRING_GENERIC_ERROR", 4);

$string_code_to_name = array(
    STRING_NO_STRING => "no_string",
    STRING_USERNAME_EXISTS => "username_exists",
    STRING_EMAIL_EXISTS => "email_exists",
    STRING_REGISTER_UNKNOWN_ERR => "register_unknown_err",
    STRING_GENERIC_ERROR => "generic_error"
);

$supported_locales = null;

function _load_strings() {
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
        $string_data = json_decode(file_get_contents(dirname(__FILE__).'/../strings.json'), true);
        $supported_locales = $string_data['supported_locales'];
        $strings = $string_data['strings'];
        if (!$supported_locales || count($supported_locales) == 0) {
            throw new UnexpectedValueException("strings.json must define at least one locale");
        }

        foreach ($string_code_to_name as $code => $string_name) {
            unset($defined_codes[$code]);
            if (!isset($strings[$string_name])) {
                throw new UnexpectedValueException("$string_name is missing from strings.json");
            }
            else {
                foreach ($supported_locales as $locale) {
                    if (!isset($strings[$string_name][$locale])) {
                        throw new UnexpectedValueException("$[$string_name] is missing '$locale' translation in strings.json");
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
 * @param $string_code int STRING_ enumeration constant
 * @return string translated string
 * @throws InvalidArgumentException in case of bad string code or locale
 */
function get_string($string_code) {
    global $string_code_to_name, $supported_locales;
    $strings = _load_strings();

    $string_name = isset($string_code_to_name[$string_code]) ? $string_code_to_name[$string_code] : null;
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

    return $strings[$string_name][$locale];
}
