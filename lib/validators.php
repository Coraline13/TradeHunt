<?php
require_once dirname(__FILE__) . '/../init.php';
require_once dirname(__FILE__) . '/strings.php';

/**
 * Create a validator for checking that a number is in the given range [$min, $max].
 * @param string $field field name for error printing
 * @param float|int $min minimum value, inclusive
 * @param float|int $max maximum value, inclusive
 * @param int $string_code error format STRING
 * @return Closure validator closure
 */
function validator_range($field, $min, $max, $string_code = STRING_VALIDATE_RANGE) {
    return function ($val) use ($min, $max, $field, $string_code) {
        if ($val < $min || $val > $max) {
            return get_string_format($string_code, $field, $min, $max);
        }
        return null;
    };
}

/**
 * Create a validator for checking that a string's length is in the given range [$min, $max].
 * @param string $field field name for error printing
 * @param int $min minimum length, inclusive
 * @param int $max maximum length, inclusive
 * @param int $string_code error format STRING
 * @return Closure validator closure
 */
function validator_string_length($field, $min, $max, $string_code = STRING_VALIDATE_LENGTH)
{
    return function ($str) use ($min, $max, $field, $string_code) {
        $len = strlen($str);
        if ($len < $min || $len > $max) {
            return get_string_format($string_code, $field, $min, $max);
        }
        return null;
    };
}

/**
 * Create a validator for checking strings against an arbitrary regular expression.
 * @param string $regex regex to match against the string
 * @param string $field field name for error printing
 * @param int $string_code error format STRING
 * @return Closure validator closure
 */
function validator_regex($field, $regex, $string_code = STRING_VALIDATE_REGEX)
{
    return function ($str) use ($regex, $field, $string_code) {
        if (is_null($str) || !preg_match($regex, $str)) {
            return get_string_format($string_code, $field);
        }
        return null;
    };
}

/**
 * Create a validator for checking that no characters in a string are outisde allowed character ranges.
 * @param string $field field name for error printing
 * @param bool $lower true if ASCII lowercase characters are allowed
 * @param bool $upper true if ASCII uppercase characters are allowed
 * @param bool $digits true if digits are allowed
 * @param string $extra other characters that are allowed
 * @param int $string_code error format STRING
 * @return Closure validator closure
 */
function validator_charset($field, $lower, $upper, $digits, $extra = "", $string_code = STRING_VALIDATE_CHARSET)
{
    $charset = ($lower ? "a-z" : "") . ($upper ? "A-Z" : "") . ($digits ? "0-9" : "") . $extra;
    return function ($str) use ($charset, $field, $lower, $upper, $digits, $extra, $string_code) {
        foreach (str_split($str) as $chr) {
            $good = strpos($extra, $chr) !== false;
            $good = $good || ($chr >= '0' && $chr <= '9' && $digits);
            $good = $good || ($chr >= 'A' && $chr <= 'Z' && $upper);
            $good = $good || ($chr >= 'a' && $chr <= 'z' && $lower);

            if (!$good) {
                return get_string_format($string_code, $field, $charset);
            }
        }
        return null;
    };
}

/**
 * Create a validator for checking the syntactic validity of an e-mail address.
 * @param int $string_code error format STRING
 * @return Closure validator closure
 */
function validator_email($string_code = STRING_VALIDATE_EMAIL) {
    static $email_regex = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD';
    return function ($str) use ($string_code, $email_regex) {
        if (!preg_match($email_regex, $str)) {
            return get_string_format($string_code, $str);
        }
        return null;
    };
}

/**
 * Create a validator for checking the syntactic validity of a phone number.
 * @param int $string_code error format STRING
 * @return Closure validator closure
 */
function validator_phone_number($string_code = STRING_VALIDATE_PHONE) {
    return function ($str) use($string_code) {
        return null;
    };
}
