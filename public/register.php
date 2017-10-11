<?php
require_once dirname(__FILE__) . '/../lib/api.php';

$username = '';
$email = '';
$first_name = '';
$last_name = '';
$tel = '';
/** @var Location $location */
$location = null;
/** @var APIException $form_error */
$form_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $first_name = validate_array_value($_POST, 'first_name', [validator_string_length(get_string(STRING_FIRST_NAME), 1, CFG_NAME_MAX_LEN)]);
        $last_name = validate_array_value($_POST, 'last_name', [validator_string_length(get_string(STRING_LAST_NAME), 1, CFG_NAME_MAX_LEN)]);
        $tel = require_array_value($_POST, 'tel', false);
        $tel = validate_value($tel, 'tel', [validator_phone_number()]);

        $username = validate_array_value($_POST, 'username', [
            validator_string_length(get_string(STRING_USERNAME), CFG_USERNAME_MIN_LEN, CFG_USERNAME_MAX_LEN),
            validator_regex(get_string(STRING_USERNAME), '/' . CFG_USERNAME_REGEX . '/')
        ]);
        $email = validate_array_value($_POST, 'email', [validator_email()]);
        $password = validate_array_value($_POST, 'password', [validator_string_length(get_string(STRING_PASSWORD), CFG_PASSWORD_MIN_LEN, CFG_PASSWORD_MAX_LEN)]);

        $db->beginTransaction();
        $location = Location::getById(require_array_value($_POST, 'location_id', false));
        $profile = Profile::create($location, $first_name, $last_name, $tel);
        $user = User::create($username, $email, $password, $profile);
        $db->commit();

        header('Location: /', true, 303);
        exit();
    } catch (APIException $e) {
        $form_error = $e;
        http_response_code($e->getRecommendedHttpStatus());
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo _t('u', STRING_REGISTER) ?></title>
    <link rel="stylesheet" type="text/css" href="static/css/regsiter.css"/>

    <script type="text/javascript">
        window.onload = function () {
            document.getElementById("password").onchange = validatePassword;
            document.getElementById("repeat_password").onchange = validatePassword;
        };

        function validatePassword() {
            var pass1 = document.getElementById("password").value;
            var pass2 = document.getElementById("repeat_password").value;
            if (pass1 !== pass2) {
                document.getElementById("repeat_password").setCustomValidity("<?php echo _t('u', STRING_PASSWORD_MISMATCH) ?>");
            }
            else {
                document.getElementById("repeat_password").setCustomValidity('');
            }
        }
    </script>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    <div class="error" <?php echo $form_error ? "" : "hidden" ?>>
        <?php
        if ($form_error) {
            echo $form_error->getMessage() . '<br/>';
            if ($form_error instanceof ValidationException) {
                echo $form_error->getArgName() . ': ' . $form_error->getValidationError();
            }
        }
        ?>
    </div>
    <fieldset>
        <legend><?php echo _t('u', STRING_LOG_IN) ?></legend>
        <div>
            <label for="username"><?php echo _t('u', STRING_USERNAME) ?>:</label>
            <input type="text" name="username" id="username" required
                   placeholder="<?php echo _t('l', STRING_USERNAME) ?>"
                   pattern="<?php echo CFG_USERNAME_REGEX ?>"
                   minlength="<?php echo CFG_USERNAME_MIN_LEN ?>" maxlength="<?php echo CFG_USERNAME_MAX_LEN ?>"
                   value="<?php echo $username ?>">

        </div>
        <div>
            <label for="email"><?php echo _t('u', STRING_EMAIL_ADDRESS) ?>:</label>
            <input type="email" name="email" id="email" required
                   placeholder="<?php echo _t('l', STRING_EMAIL_ADDRESS) ?>"
                   value="<?php echo $email ?>">
        </div>
        <div>
            <label for="password"><?php echo _t('u', STRING_PASSWORD) ?>:</label>
            <input type="password" name="password" id="password" required
                   placeholder="<?php echo _t('l', STRING_PASSWORD) ?>"
                   minlength="<?php echo CFG_PASSWORD_MIN_LEN ?>" maxlength="<?php echo CFG_PASSWORD_MAX_LEN ?>">

        </div>
        <div>
            <label for="repeat_password"><?php echo _t('u', STRING_REPEAT_PASSWORD) ?>:</label>
            <input type="password" name="repeat_password" id="repeat_password" required
                   placeholder="<?php echo _t('l', STRING_REPEAT_PASSWORD) ?>">

        </div>
    </fieldset>

    <fieldset>
        <legend><?php echo _t('u', STRING_PERSONAL_INFO) ?></legend>
        <div>
            <label for="first_name"><?php echo _t('u', STRING_FIRST_NAME) ?>:</label>
            <input type="text" name="first_name" id="first_name" required
                   placeholder="<?php echo _t('l', STRING_FIRST_NAME) ?>"
                   maxlength="<?php echo CFG_NAME_MAX_LEN ?>"
                   value="<?php echo $first_name ?>">
        </div>
        <div>
            <label for="last_name"><?php echo _t('u', STRING_LAST_NAME) ?>: </label>
            <input type="text" name="last_name" id="last_name" required
                   placeholder="<?php echo _t('l', STRING_LAST_NAME) ?>"
                   maxlength="<?php echo CFG_NAME_MAX_LEN ?>"
                   value="<?php echo $last_name ?>">

        </div>
        <div>
            <label for="tel"><?php echo _t('u', STRING_PHONE_NUMBER) ?>:</label>
            <input type="tel" name="tel" id="tel" required
                   placeholder="<?php echo _t('l', STRING_PHONE_NUMBER) ?>"
                   value="<?php echo $tel ?>">
        </div>
        <div>
            <label for="location"><?php echo _t('u', STRING_LOCATION) ?>:</label>
            <select name="location_id" id="location" required>
                <option value=""></option>
                <?php
                foreach (Location::getAll() as $loc) {
                    $selected = $location != null && $loc->getId() == $location->getId();
                    echo '<option value="' . $loc->getId() . '" ' . ($selected ? "selected" : "") . '>';
                    echo $loc->getCountry() . ' - ' . $loc->getCity();
                    echo '</option>\n';
                }
                ?>
            </select>
        </div>
    </fieldset>

    <div>
        <input type="submit" value="<?php echo _t('u', STRING_REGISTER) ?>">
    </div>
</form>
</body>
</html>
