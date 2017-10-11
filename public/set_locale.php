<?php
require_once dirname(__FILE__).'/../lib/api.php';

global $_LOCALE, $_SUPPORTED_LOCALES;
check_method(["GET"]);

$locale = isset($_GET['locale']) ? $_GET['locale'] : null;
if (empty($locale) || !in_array($locale, $_SUPPORTED_LOCALES)) {
    throw new ValidationException('locale', "locale code not set or invalid ($locale)");
}

$_LOCALE = $locale;
setcookie(CFG_COOKIE_LOCALE, $_LOCALE, time() + 60*60*24*365 /*1 year*/, "/");
header('Location: ' . $_SERVER['HTTP_REFERER'], true, 303);
