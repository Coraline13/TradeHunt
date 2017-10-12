<?php
require_once dirname(__FILE__).'/../lib/api.php';

global $_LOCALE, $_SUPPORTED_LOCALES;
$GLOBALS['included_from'] = "index";

if (empty($_USER)) {
    require dirname(__FILE__).'/welcome.php';
}
else {
    require dirname(__FILE__).'/profile.php';
}

