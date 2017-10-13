<?php
require_once dirname(__FILE__).'/../lib/api.php';

global $_USER;
check_method(["GET", "POST"]);
force_authentication(true);

$recipient = User::getById(require_array_value($_GET, 'user_id', false));
$prechecked_id = isset($_GET['prechecked_id']) ? (int)$_GET['prechecked_id'] : 0;
$recipient_name = $recipient->getProfile()->getFirstName();

$message = '';
/** @var int[] $listing_ids */
$listing_ids = [];

/** @var APIException $form_error_trade */
$form_error_trade = null;

/** @var Trade $trade */
$trade = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $message = validate_array_value($_POST, 'message', [validator_string_length(get_string(STRING_TRADE_MESSAGE), 0, CFG_TRADE_MESSAGE_MAX_LEN)]);

        $listing_ids = require_array_value($_POST, 'listing_ids', false);

        $db->beginTransaction();
        $trade = Trade::create($_USER, $recipient, $message);

        foreach ($listing_ids as $lid) {
            $trade->addListing(Listing::getById($lid));
        }
        $db->commit();

        log_info($_USER->getUsername()." sent trade ".$trade->getId()." to ".$recipient->getUsername());
        http_redirect("profile.php", 303);
        exit();
    } catch (APIException $e) {
        $form_error_trade = $e;
        http_response_code($e->getRecommendedHttpStatus());
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo _t('u', STRING_MAKE_TRADE, $recipient_name) ?></title>
    <link rel="icon" href="favicon.ico" type="image/x-icon"/>

    <link rel="stylesheet" type="text/css" href="static/css/style.css"/>
</head>
<body>
<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
    <div class="error" <?php echo $form_error_trade ? "" : "hidden" ?>>
        <?php
        if ($form_error_trade) {
            echo $form_error_trade->getMessage().'<br/>';
            if ($form_error_trade instanceof ValidationException) {
                echo $form_error_trade->getArgName().': '.$form_error_trade->getValidationError();
            }
        }
        ?>
    </div>

    <div class="success" <?php echo $trade ? "" : "hidden" ?>>
        <?php
        if ($trade) {
            echo 'Created trade '.$trade->getId();
        }
        ?>
    </div>

    <fieldset>
        <legend><?php echo _t('u', STRING_MAKE_TRADE, $recipient_name) ?></legend>
        <div class="form-group">
            <label for="message"><?php echo _t('u', STRING_TRADE_MESSAGE_PLACEHOLDER, $recipient_name) ?>:</label>
            <textarea class="form-control" name="message" id="message"
                      placeholder="<?php echo _t('l', STRING_TRADE_MESSAGE_PLACEHOLDER, $recipient_name) ?>"
                      maxlength="<?php echo CFG_TRADE_MESSAGE_MAX_LEN ?>"><?php echo $message ?></textarea>
        </div>

        <div class="form-group">
            <label class="control-label"><?php echo _t('u', STRING_WHAT_OFFER) ?></label>
            <ul class="trade-listings">
                <?php foreach ($_USER->getListings([Listing::TYPE_OFFER]) as $listing):
                    include dirname(__FILE__).'/../template/trade-listing-li.php';
                endforeach; ?>
            </ul>
        </div>

        <div class="form-group">
            <label class="control-label"><?php echo _t('u', STRING_WHAT_WANT) ?></label>
            <ul class="trade-listings">
                <?php foreach ($recipient->getListings([Listing::TYPE_OFFER]) as $listing):
                    include dirname(__FILE__).'/../template/trade-listing-li.php';
                endforeach; ?>
            </ul>
        </div>
    </fieldset>

    <div class="form-group">
        <button type="submit" name="action" value="add_trade" class="btn btn-xl">
            <?php echo _t('u', STRING_ACTION_SEND) ?>
        </button>
    </div>
</form>

<footer><?php include dirname(__FILE__).'/../template/select-lang.php' ?></footer>
</body>
</html>
