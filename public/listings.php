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
                                <li>Books</li>
                                <li>Articles</li>
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
            $lcnt = 1;
            $listings = Listing::getPaged('new', 0);
            foreach ($listings as $listing) {
                $image = $listing->getMainImageURL();
                $slug = $listing->getSlug();
                $title = $listing->getTitle();
                $tags = implode(", ", array_map(function (Tag $tag) {
                    return $tag->getName();
                }, $listing->getTags()));

                $profile = $listing->getUser()->getProfile();
                $name = $profile->getFirstName().' '.$profile->getLastName();
                $added = strftime("%x", $listing->getAdded()->getTimestamp());

                echo '<div class="col-md-4 col-sm-6 listing-item">';
                echo "    <a href=\"#listingModal$lcnt\" class=\"listing-link\" data-toggle=\"modal\">";
                echo '        <div class="listing-hover"><div class="listing-hover-content">';
                echo '            <i class="fa fa-plus fa-3x"></i>';
                echo '        </div></div>';
                echo "        <img src=\"$image\" class=\"img-responsive\" alt=\"$slug\">";
                echo '    </a>';
                echo '    <div class="listing-caption">';
                echo "        <h4>$title</h4>";
                echo "        <p class=\"text-muted\">$name | $added</p>";
                echo '    </div>';
                echo '</div>';
                $lcnt += 1;
            }

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

<?php $lcnt = 1;
foreach ($listings as $listing): ?>
    <div class="listing-modal modal fade" id="listingModal<?php echo $lcnt ?>" tabindex="-1" role="dialog"
         aria-hidden="true">
        <div class="modal-content">
            <div class="close-modal" data-dismiss="modal">
                <div class="lr">
                    <div class="rl">
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-lg-offset-2">
                        <div class="modal-body">
                            <?php
                            $profile = $listing->getUser()->getProfile();
                            $name = $profile->getFirstName().' '.$profile->getLastName();
                            $added = strftime("%x", $listing->getAdded()->getTimestamp())
                            ?>
                            <!-- Project Details Go Here -->
                            <h2><?php echo $listing->getTitle() ?></h2>
                            <p class="item-intro text-muted"><?php echo $name.' | '.$added ?></p>
                            <img class="img-responsive" src="<?php echo $listing->getMainImageURL() ?>"
                                 alt="<?php echo $listing->getSlug() ?>">
                            <p><?php echo $listing->getDescription() ?></p>
                            <button type="button" class="btn btn-primary" data-dismiss="modal"><i
                                    class="fa fa-times"></i>PLACEHOLDER
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    $lcnt += 1;
endforeach; ?>

</body>
</html>