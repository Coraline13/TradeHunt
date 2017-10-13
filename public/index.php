<?php
require_once dirname(__FILE__).'/../lib/api.php';

global $_LOCALE, $_SUPPORTED_LOCALES;
$GLOBALS['included_from'] = "index";

if (strpos($_SERVER['REQUEST_URI'], 'index.php')) {
    http_redirect("", 307, false, true);
}

if (empty($_USER)) {
    require dirname(__FILE__).'/welcome.php';
}
else {
    require dirname(__FILE__).'/listings.php';
}

