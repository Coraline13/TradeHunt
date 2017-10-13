<?php
require_once dirname(__FILE__).'/../lib/api.php';

check_method(["GET"]);
force_authentication(true);

global $_USER;
$profile = $_USER->getProfile();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo _t('u', STRING_USER_PROFILE, $_USER->getUsername()) ?></title>
    <link rel="icon" href="favicon.ico" type="image/x-icon"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel='stylesheet prefetch' href='static/lib/jquery-ui.css'>
    <link rel='stylesheet prefetch' href='static/lib/bootstrap.min.css'>
    <link rel="stylesheet" href="static/lib/font-awesome.min.css">
    <link rel="stylesheet" href="static/css/celine.css">
    <link rel="stylesheet" href="static/css/style.css">
    <link rel="stylesheet" href="static/css/search.css">
    <link rel="stylesheet" href="static/lib/language_selector.css">

    <script src="static/lib/language_selector.js"></script>
    <script src='static/lib/jquery.min.js'></script>
    <script src='static/lib/jquery-ui.min.js'></script>
    <script src='static/lib/bootstrap.min.js'></script>
    <!-- Plugin JavaScript -->
    <script src="static/lib/jquery.easing.min.js"></script>
    <script src="static/js/search.js"></script>
    <script src="static/js/celine.js"></script>

</head>

<body id="page-top">

<!-- Navigation -->
<nav class="navbar navbar-default navbar-fixed-top navbar-shrink">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header page-scroll">
            <button type="button" class="navbar-toggle" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand page-scroll" href="index.php"><img class="logo" src="static/img/logo.svg"></a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
                <li class="hidden active">
                    <a href="#page-top"></a>
                </li>
                <li class="">
                    <?php include dirname(__FILE__).'/../template/select-lang.php' ?>
                </li>
                <li class="">
                    <a class="page-scroll" href="listings.php"><?php echo _t('u', STRING_LISTINGS) ?></a>
                </li>
                <li class="">
                    <a class="page-scroll" href="logout.php"><?php echo _t('u', STRING_LOGOUT) ?></a>
                </li>
            </ul>
        </div>
        <!-- /.navbar-collapse -->
    </div>
    <!-- /.container-fluid -->
</nav>

<!-- Team Section -->
<section id="team">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="section-heading"><?php echo _t('u', STRING_HELLO, $profile->getFirstName()) ?></h2>
                <h3 class="section-subheading text-muted"><?php echo _t('u', STRING_WHATS_UP) ?></h3>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div>
                    <legend><h4 class="service-heading"><?php echo _t('u', STRING_PERSONAL_INFO) ?></h4></legend>
                    <p class="text-muted"><b><?php echo _t('u', STRING_USERNAME).': '?></b><?php echo $_USER->getUsername()?></p>
                    <p class="text-muted"><b><?php echo _t('u', STRING_EMAIL_ADDRESS).': '?></b><?php echo $_USER->getEmail()?></p>
                    <p class="text-muted"><b><?php echo _t('u', STRING_FIRST_NAME).': '?></b><?php echo $_USER->getProfile()->getFirstName()?></p>
                    <p class="text-muted"><b><?php echo _t('u', STRING_LAST_NAME).': '?></b><?php echo $_USER->getProfile()->getLastName()?></p>
                    <p class="text-muted"><b><?php echo _t('u', STRING_PHONE_NUMBER).': '?></b><?php echo $_USER->getProfile()->getTel()?></p>
                    <p class="text-muted"><b><?php echo _t('u', STRING_LOCATION).': '?></b><?php echo $_USER->getProfile()->getLocation()->getCountry().' - '.$_USER->getProfile()->getLocation()->getCity()?></p>
            </div>
            </div>
            <div class="col-sm-6">
                <div>
                    <legend><h4 class="service-heading"><?php echo _t('u', STRING_YOUR_TRADES) ?></h4></legend>
                    <?php echo print_r($_USER->getTrades()) ?>
                </div>
            </div>
        </div>
    </div>
</section>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <span class="copyright">Copyright © Trade Hunt 2017</span>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
</footer>

</body>
</html>
