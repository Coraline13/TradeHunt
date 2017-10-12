<?php
require_once dirname(__FILE__).'/../lib/api.php';

global $_USER;
$GLOBALS['root'] = "";

check_method(["GET", "POST"]);
force_authentication(true);

$type = 0;
$title = '';
$description = '';
/** @var Location $location */
$location = $_USER->getProfile()->getLocation();
/** @var APIException $form_error_listing */
$form_error_listing = null;

/** @var Listing $listing */
$listing = null;

/**
 * Simple slugify function for PHP. Creates a slug for the passed string, taking into account international characters as well.
 * @see https://gist.github.com/james2doyle/9158349
 * @param string $string textual name to be slugified
 * @param array $replace characters from $string to remove
 * @param string $delimiter slug component delimiter
 * @return mixed|string slugified name
 */
function slugify($string, $replace = array(), $delimiter = '-')
{
    // https://github.com/phalcon/incubator/blob/master/Library/Phalcon/Utils/Slug.php
    if (!extension_loaded('iconv')) {
        throw new RuntimeException('iconv module not loaded');
    }
    // Save the old locale and set the new locale to UTF-8
    $oldLocale = setlocale(LC_ALL, '0');
    setlocale(LC_ALL, 'en_US.UTF-8');
    $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    if (!empty($replace)) {
        $clean = str_replace((array)$replace, ' ', $clean);
    }
    $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower($clean);
    $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
    $clean = trim($clean, $delimiter);
    // Revert back to the old locale
    setlocale(LC_ALL, $oldLocale);
    return $clean;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $type = (int)require_array_value($_POST, 'type', false);
        $title = validate_array_value($_POST, 'title', [validator_string_length(get_string(STRING_TITLE), CFG_LISTING_TITLE_MIN_LEN, CFG_LISTING_TITLE_MAX_LEN)]);
        $description = validate_array_value($_POST, 'description', [validator_string_length(get_string(STRING_DESCRIPTION), 0, CFG_LISTING_DESCRIPTION_MAX_LEN)]);
        $slug = slugify($title).'-'.rand(10000, 99999);

        $images = [];
        $fdata = $_FILES['images'];
        if (is_array($fdata['name'])) {
            for ($i = 0; $i < count($fdata['name']); ++$i) {
                $images[] = [
                    'name' => $fdata['name'][$i],
                    'tmp_name' => $fdata['tmp_name'][$i],
                ];
            }
        } else {
            $images = $fdata;
        }

        $db->beginTransaction();
        $location = Location::getById(require_array_value($_POST, 'location_id', false));
        $listing = Listing::create($type, $_USER, $title, $slug, $description, $location);
        $icnt = 0;
        foreach ($images as $image) {
            $image_type = exif_imagetype($image['tmp_name']);
            $ext = image_type_to_extension($image_type, false);
            $image_name = sprintf("/img/listings/%s_%d.%s", $listing->getSlug(), $icnt, $ext);
            move_uploaded_file($image['tmp_name'], dirname(__FILE__).$image_name);
            Image::create($image_name, $listing);
            $icnt += 1;
        }
        $db->commit();

        log_info($_USER->getUsername()." added listing ".$listing->getId()." ".$listing->getTitle());
        header('Location: profile.php', true, 303);
        exit();
    } catch (APIException $e) {
        $form_error_listing = $e;
        http_response_code($e->getRecommendedHttpStatus());
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo _t('u', STRING_ADD_LISTING) ?></title>
    <link rel="icon" href="favicon.ico" type="image/x-icon"/>

    <link rel="stylesheet" type="text/css" href="static/css/style.css"/>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
    <div class="error" <?php echo $form_error_listing ? "" : "hidden" ?>>
        <?php
        if ($form_error_listing) {
            echo $form_error_listing->getMessage().'<br/>';
            if ($form_error_listing instanceof ValidationException) {
                echo $form_error_listing->getArgName().': '.$form_error_listing->getValidationError();
            }
        }
        ?>
    </div>

    <div class="success" <?php echo $listing ? "" : "hidden" ?>>
        <?php
        if ($listing) {
            echo 'Created listing '.$listing->getSlug();
        }
        ?>
    </div>

    <fieldset>
        <legend><?php echo _t('u', STRING_ADD_LISTING) ?></legend>
        <div class="form-group">
            <label for="title"><?php echo _t('u', STRING_TITLE) ?>:</label>
            <input type="text" class="form-control" name="title" id="title" required
                   placeholder="<?php echo _t('l', STRING_TITLE) ?>"
                   minlength="<?php echo CFG_LISTING_TITLE_MIN_LEN ?>"
                   maxlength="<?php echo CFG_LISTING_TITLE_MAX_LEN ?>"
                   value="<?php echo $title ?>">

        </div>
        <div class="form-group">
            <label for="description"><?php echo _t('u', STRING_DESCRIPTION) ?>:</label>
            <textarea class="form-control" name="description" id="description" required
                      placeholder="<?php echo _t('l', STRING_DESCRIPTION_PLACEHOLDER) ?>"
                      maxlength="<?php echo CFG_LISTING_DESCRIPTION_MAX_LEN ?>"><?php echo $description ?></textarea>
        </div>

        <div class="form-group">
            <label for="location"><?php echo _t('u', STRING_LOCATION) ?>:</label>
            <select class="form-control" name="location_id" id="location" required>
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

        <fieldset>
            <legend><?php echo _t('u', STRING_LISTING_TYPE) ?></legend>
            <div>
                <input type="radio" id="type1" name="type" value="1">
                <label for="type1"><?php echo _t(null, STRING_LISTING_OFFER) ?></label>

                <input type="radio" id="type2" name="type" value="2">
                <label for="type2"><?php echo _t(null, STRING_LISTING_WISH) ?></label>
            </div>
        </fieldset>

        <input type="file" name="images[]" id="images" accept="image/*" multiple/>
    </fieldset>

    <div class="form-group">
        <button type="submit" name="action" value="add_listing" class="btn btn-xl">
            <?php echo _t('u', STRING_ACTION_ADD) ?>
        </button>
    </div>
</form>

<footer><?php include dirname(__FILE__).'/../lib/select-lang.php' ?></footer>
</body>
</html>
