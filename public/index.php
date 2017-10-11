<?php
require_once dirname(__FILE__).'/../lib/api.php';

global $_LOCALE, $_SUPPORTED_LOCALES;
$GLOBALS['root'] = "";

check_method(["GET"]);

if (empty($_USER)) {
    header('Location: welcome.php', true, 302);
}
else {
    header('Location: profile.php', true, 302);
}

