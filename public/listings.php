<?php
require_once dirname(__FILE__).'/../lib/api.php';

global $_USER;
check_method(["GET", "POST"]);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo _t('u', STRING_RECENT_POSTS) ?></title>
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

<body id="page-top" class="index" data-pinterest-extension-installed="cr1.3.4">

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
            <a class="navbar-brand page-scroll" href="#page-top"><img class="logo" src="static/img/logo.svg"></a>
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
                    <a class="page-scroll" href="listing.php"><?php echo _t('u', STRING_ADD_LISTING) ?></a>
                </li>
                <li class="">
                    <a class="page-scroll" href="profile.php"><?php echo _t('u', STRING_MY_PROFILE) ?></a>
                </li>
            </ul>
        </div>
        <!-- /.navbar-collapse -->
    </div>
    <!-- /.container-fluid -->
</nav>

<!-- Listings Section -->
<section id="listings" class="bg-light-gray">
    <div class="container">
        <div class="row">
            <div class="filter-toolbar col-lg-12 text-center">
                <div class="col-md-8">
                    <form class="search_bar small">
                        <div class="search_dropdown" style="width: 16px;">
                            <span><?php echo _t('u', STRING_CATEGORIES) ?></span>
                            <ul>
                                <li class="selected"><?php echo _t('u', STRING_CATEGORIES) ?></li>
                                <?php
                                foreach (Tag::getAll() as $tag) {
                                    echo "<li>".$tag->getName()."</li>";
                                }
                                ?>
                            </ul>
                        </div>
                        <input type="text" placeholder="<?php echo _t('u', STRING_SEARCH) ?>"/>
                        <button type="submit" value="Search">Search</button>
                    </form>
                </div>
                <div class="col-md-3"></div>
                <div class="col-md-1">
                    <a class="add-listing" href="listing.php"><i class="fa fa-plus"></i></a>
                </div>
            </div>
        </div>
        <div class="row">
            <?php
            $listings = Listing::getPaged('new', 0);
            include dirname(__FILE__).'/../template/listing-cards.php'
            ?>
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

<?php include dirname(__FILE__).'/../template/listing-modals.php' ?>

</body>
</html>