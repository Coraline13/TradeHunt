<?php
require_once dirname(__FILE__).'/../lib/api.php';

global $_USER;
check_method(["GET", "POST"]);
force_authentication(false);
if (!isset($GLOBALS['included_from']) || $GLOBALS['included_from'] !== 'index') {
    http_redirect("", 301);
}

/** @var APIException $form_error_login */
$form_error_login = null;
/** @var APIException $form_error_register */
$form_error_register = null;

// register form
$username = '';
$email = '';
$first_name = '';
$last_name = '';
$tel = '';
/** @var Location $location */
$location = null;

// login form
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    if ($action == 'login') {
        try {
            $login = $_POST['login'];
            $password = $_POST['password'];

            $db->beginTransaction();
            $user = User::getByNameOrEmail($login);
            $session = $user->authenticate($password);
            $db->commit();

            log_info("Successful login for ".$user->getUsername());
            http_redirect("", 303);
            exit();
        } catch (APIException $e) {
            $form_error_login = $e;
            http_response_code($e->getRecommendedHttpStatus());
        }
    } else if ($action == 'register') {
        try {
            $first_name = validate_array_value($_POST, 'first_name', [validator_string_length(get_string(STRING_FIRST_NAME), 1, CFG_NAME_MAX_LEN)]);
            $last_name = validate_array_value($_POST, 'last_name', [validator_string_length(get_string(STRING_LAST_NAME), 1, CFG_NAME_MAX_LEN)]);
            $tel = require_array_value($_POST, 'tel', false);
            $tel = validate_value($tel, 'tel', [validator_phone_number()]);

            $username = validate_array_value($_POST, 'username', [
                validator_string_length(get_string(STRING_USERNAME), CFG_USERNAME_MIN_LEN, CFG_USERNAME_MAX_LEN),
                validator_regex(get_string(STRING_USERNAME), '/'.CFG_USERNAME_REGEX.'/'),
            ]);
            $email = validate_array_value($_POST, 'email', [validator_email()]);
            $password = validate_array_value($_POST, 'password', [validator_string_length(get_string(STRING_PASSWORD), CFG_PASSWORD_MIN_LEN, CFG_PASSWORD_MAX_LEN)]);

            $db->beginTransaction();
            $location = Location::getById(require_array_value($_POST, 'location_id', false));
            $profile = Profile::create($location, $first_name, $last_name, $tel);
            $user = User::create($username, $email, $password, $profile);
            $db->commit();

            $db->beginTransaction();
            $session = $user->openSession();
            $db->commit();

            http_redirect("", 303);
            exit();
        } catch (APIException $e) {
            $form_error_register = $e;
            http_response_code($e->getRecommendedHttpStatus());
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo _t('t', STRING_APP_NAME) ?></title>
    <link rel="icon" href="favicon.ico" type="image/x-icon"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel='stylesheet prefetch' href='static/lib/jquery-ui.css'>
    <link rel='stylesheet prefetch' href='static/lib/bootstrap.min.css'>
    <link rel="stylesheet" href="static/lib/font-awesome.min.css">
    <link rel="stylesheet" href="static/css/celine.css">
    <link rel="stylesheet" href="static/css/style.css">
    <link rel="stylesheet" href="static/lib/language_selector.css">

    <script src="static/lib/language_selector.js"></script>
    <script src='static/lib/jquery.min.js'></script>
    <script src='static/lib/jquery-ui.min.js'></script>
    <script src='static/lib/bootstrap.min.js'></script>
    <!-- Plugin JavaScript -->
    <script src="static/lib/jquery.easing.min.js"></script>

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
                    <a class="page-scroll" href="#services"><?php echo _t('u', STRING_SERVICES) ?></a>
                </li>
                <li class="">
                    <a class="page-scroll" href="#listings"><?php echo _t('u', STRING_LISTINGS) ?></a>
                </li>
<!--                <li class="">-->
<!--                    <a class="page-scroll" href="#about">About</a>-->
<!--                </li>-->
                <li class="">
                    <a class="page-scroll" href="#team"><?php echo _t('u', STRING_TEAM) ?></a>
                </li>
                <li class="">
                    <a class="page-scroll" href="#auth"><?php echo _t('u', STRING_LOG_IN) ?></a>
                </li>
            </ul>
        </div>
        <!-- /.navbar-collapse -->
    </div>
    <!-- /.container-fluid -->
</nav>

<!-- Header -->
<header>
    <div class="container">
        <div class="intro-text">
            <div class="intro-lead-in"><?php echo _t('u', STRING_INTRO_LEAD_IN) ?></div>
            <div class="intro-heading"><?php echo _t('u', STRING_INTRO_TEXT) ?></div>
            <a href="#auth" class="page-scroll btn btn-xl"><?php echo _t('u', STRING_REGISTER) ?></a>
        </div>
    </div>
</header>

<!-- Services Section -->
<section id="services">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h2 class="section-heading"><?php echo _t('u', STRING_SERVICES) ?></h2>
                <h3 class="section-subheading text-muted"><?php echo _t('u', STRING_SERVICES_DESCR) ?></h3>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fa fa-circle fa-stack-2x text-primary"></i>
                        <img class="service-img search" src="static/img/search.svg">
                    </span>
                <h4 class="service-heading"><?php echo _t('u', STRING_SERVICES_FIND) ?></h4>
                <p class="text-muted"><?php echo _t('u', STRING_SERVICES_FIND_DESCR) ?></p>
            </div>
            <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fa fa-circle fa-stack-2x text-primary"></i>
                        <img class="service-img" src="static/img/box.svg">
                    </span>
                <h4 class="service-heading"><?php echo _t('u', STRING_SERVICES_GET_RID) ?></h4>
                <p class="text-muted"><?php echo _t('u', STRING_SERVICES_GET_RID_DESCR) ?></p>
            </div>
            <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fa fa-circle fa-stack-2x text-primary"></i>
                        <img class="service-img" src="static/img/telescope.svg">
                    </span>
                <h4 class="service-heading"><?php echo _t('u', STRING_SERVICES_DISCOVER) ?></h4>
                <p class="text-muted"><?php echo _t('u', STRING_SERVICES_DISCOVER_DESCR) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Listings Section -->
<section id="listings" class="bg-light-gray">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h2 class="section-heading"><?php echo _t('u', STRING_RECENT_POSTS) ?></h2>
                <h3 class="section-subheading text-muted"><?php echo _t('u', STRING_RECENT_POSTS_DESCR) ?></h3>
            </div>
        </div>
        <div class="row">
            <?php
            $listings = Listing::getPaged('new', 0, 6);
            include dirname(__FILE__).'/../template/listing-cards.php'
            ?>
        </div>
    </div>
</section>

<!-- Team Section -->
<section id="team">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h2 class="section-heading"><?php echo _t('u', STRING_OUR_TEAM) ?></h2>
                <h3 class="section-subheading text-muted"><?php echo _t('u', STRING_TEAM_DESCR) ?></h3>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="team-member">
                    <img src="static/img/cora.png" class="img-responsive img-circle" alt="">
                    <h4>Coralia Bodea</h4>
                    <p class="text-muted">Lead Designer</p>
                    <ul class="list-inline social-buttons">
                        <li><a href="https://codepen.io/coraline13/#" target="_blank"><i class="fa fa-codepen"></i></a>
                        </li>
                        <li><a href="https://www.facebook.com/coralia.bodea" target="_blank"><i class="fa fa-facebook"></i></a>
                        </li>
                        <li><a href="https://github.com/Coraline13" target="_blank"><i class="fa fa-github"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="team-member">
                    <img src="static/img/cristi.jpg" class="img-responsive img-circle" alt="">
                    <h4>Cristi Vîjdea</h4>
                    <p class="text-muted">Lead Developer</p>
                    <ul class="list-inline social-buttons">
                        <li><a href="https://www.linkedin.com/in/cvijdea/" target="_blank"><i class="fa fa-linkedin"></i></a>
                        </li>
                        <li><a href="https://www.facebook.com/cvijdea?fref=ts&ref=br_tf" target="_blank"><i class="fa fa-facebook"></i></a>
                        </li>
                        <li><a href="https://github.com/axnsan12" target="_blank"><i class="fa fa-github"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Clients Aside -->
<section id="auth" class="bg-light-gray">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="container col-md-5 auth-container">
                    <h2 class="section-heading"><?php echo _t('u', STRING_REGISTER_PAGE) ?></h2>
                    <form id="register-form" action="<?php echo $_SERVER['PHP_SELF'].'#auth' ?>" method="post">
                        <div class="error" <?php echo $form_error_register ? "" : "hidden" ?>>
                            <?php
                            if ($form_error_register) {
                                echo $form_error_register->getMessage().'<br/>';
                                if ($form_error_register instanceof ValidationException) {
                                    echo $form_error_register->getArgName().': '.$form_error_register->getValidationError();
                                }
                            }
                            ?>
                        </div>
                        <fieldset>
                            <legend><?php echo _t('u', STRING_LOGIN_INFO) ?></legend>
                            <div class="form-group">
                                <label for="username"><?php echo _t('u', STRING_USERNAME) ?>:</label>
                                <input type="text" class="form-control" name="username" id="username" required
                                       placeholder="<?php echo _t('l', STRING_USERNAME) ?>"
                                       pattern="<?php echo CFG_USERNAME_REGEX ?>"
                                       minlength="<?php echo CFG_USERNAME_MIN_LEN ?>"
                                   maxlength="<?php echo CFG_USERNAME_MAX_LEN ?>"
                                       value="<?php echo $username ?>">

                            </div>
                            <div class="form-group">
                                <label for="email"><?php echo _t('u', STRING_EMAIL_ADDRESS) ?>:</label>
                                <input type="email" class="form-control" name="email" id="email" required
                                       placeholder="<?php echo _t('l', STRING_EMAIL_ADDRESS) ?>"
                                       value="<?php echo $email ?>">
                            </div>
                            <div class="form-group">
                                <label for="password_register"><?php echo _t('u', STRING_PASSWORD) ?>:</label>
                                <input type="password" class="form-control" name="password" id="password_register" required
                                       placeholder="<?php echo _t('l', STRING_PASSWORD) ?>"
                                       minlength="<?php echo CFG_PASSWORD_MIN_LEN ?>"
                                   maxlength="<?php echo CFG_PASSWORD_MAX_LEN ?>">

                            </div>
                            <div class="form-group">
                                <label for="repeat_password"><?php echo _t('u', STRING_REPEAT_PASSWORD) ?>:</label>
                                <input type="password" class="form-control" name="repeat_password" id="repeat_password"
                                   required
                                       placeholder="<?php echo _t('l', STRING_REPEAT_PASSWORD) ?>">

                            </div>
                        </fieldset>

                        <fieldset>
                            <legend><?php echo _t('u', STRING_PERSONAL_INFO) ?></legend>
                            <div class="form-group">
                                <label for="first_name"><?php echo _t('u', STRING_FIRST_NAME) ?>:</label>
                                <input type="text" class="form-control" name="first_name" id="first_name" required
                                       placeholder="<?php echo _t('l', STRING_FIRST_NAME) ?>"
                                       maxlength="<?php echo CFG_NAME_MAX_LEN ?>"
                                       value="<?php echo $first_name ?>">
                            </div>
                            <div class="form-group">
                                <label for="last_name"><?php echo _t('u', STRING_LAST_NAME) ?>: </label>
                                <input type="text" class="form-control" name="last_name" id="last_name" required
                                       placeholder="<?php echo _t('l', STRING_LAST_NAME) ?>"
                                       maxlength="<?php echo CFG_NAME_MAX_LEN ?>"
                                       value="<?php echo $last_name ?>">

                            </div>
                            <div class="form-group">
                                <label for="tel"><?php echo _t('u', STRING_PHONE_NUMBER) ?>:</label>
                                <input type="tel" class="form-control" name="tel" id="tel" required
                                       placeholder="<?php echo _t('l', STRING_PHONE_NUMBER) ?>"
                                       value="<?php echo $tel ?>">
                            </div>
                            <div class="form-group">
                                <label for="location"><?php echo _t('u', STRING_LOCATION) ?>:</label>
                                <select class="form-control" name="location_id" id="location" required>
                                    <option value=""></option>
                                    <?php
                                    foreach (Location::getAll() as $loc) {
                                        $selected = $location != null && $loc->getId() == $location->getId();
                                        echo '<option value="'.$loc->getId().'" '.($selected ? "selected" : "").'>';
                                        echo $loc->getCountry().' - '.$loc->getCity();
                                        echo '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </fieldset>

                        <div class="form-group">
                            <button type="submit" name="action" value="register" class="btn btn-xl">
                            <?php echo _t('u', STRING_REGISTER) ?>
                        </button>
                        </div>
                    </form>
                </div>
                <div class="container col-md-2"></div>
                <div class="container col-md-5 auth-container">
                    <h2 class="section-heading"><?php echo _t('u', STRING_LOGIN_PAGE) ?></h2>
                    <form id="login-form" action="<?php echo $_SERVER['PHP_SELF'].'#auth' ?>" method="post">
                        <div class="error" <?php echo $form_error_login ? "" : "hidden" ?>>
                            <?php
                            if ($form_error_login) {
                                echo $form_error_login->getMessage().'<br/>';
                                if ($form_error_login instanceof ValidationException) {
                                    echo $form_error_login->getArgName().': '.$form_error_login->getValidationError();
                                }
                            }
                            ?>
                        </div>

                        <fieldset>
                            <legend><?php echo _t('u', STRING_LOG_IN) ?></legend>
                            <div class="form-group">
                                <label for="login"><?php echo _t('u', STRING_IDENTIFIER) ?>:</label>
                                <input type="text" class="form-control" name="login" id="login" required
                                       placeholder="<?php echo _t('l', STRING_USERNAME_OR_EMAIL) ?>"
                                       value="<?php echo $login ?>">

                            </div>
                            <div class="form-group">
                                <label for="password_login"><?php echo _t('u', STRING_PASSWORD) ?>:</label>
                                <input type="password" class="form-control" name="password" id="password_login" required
                                       placeholder="<?php echo _t('l', STRING_PASSWORD) ?>"
                                       minlength="<?php echo CFG_PASSWORD_MIN_LEN ?>"
                                   maxlength="<?php echo CFG_PASSWORD_MAX_LEN ?>">

                            </div>
                        </fieldset>

                        <div class="form-group">
                            <button type="submit" name="action" value="login" class="btn btn-xl">
                            <?php echo _t('u', STRING_LOG_IN) ?>
                        </button>
                        </div>
                    </form>
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

<?php include dirname(__FILE__).'/../template/listing-modals.php' ?>

</body>
</html>
