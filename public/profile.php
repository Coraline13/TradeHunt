<?php
require_once dirname(__FILE__).'/../lib/api.php';

check_method(["GET"]);
force_authentication(true);

global $_USER;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo _t('u', STRING_USER_PROFILE, $_USER->getUsername()) ?></title>
    <link rel="icon" href="favicon.ico" type="image/x-icon"/>

    <link rel="stylesheet" type="text/css" href="static/css/style.css"/>
</head>
<body>

<h1>Hello, <?php echo $_USER->getProfile()->getFirstName().' '.$_USER->getProfile()->getLastName() ?></h1>

<?php echo print_r($_USER->getTrades()) ?>

<form action="logout.php" method="post">
    <button type="submit"><?php echo _t('u', STRING_LOGOUT) ?></button>
</form>

<footer><?php include dirname(__FILE__).'/../template/select-lang.php' ?></footer>
</body>
</html>
