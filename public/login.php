<?php
require_once dirname(__FILE__) . '/../lib/api.php';

$login = '';
$password = '';
/** @var APIException $form_error */
$form_error = null;
/** @var Session $session */
$session = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $login = $_POST['login'];
        $password = $_POST['password'];

        $db->beginTransaction();
        $user = User::getByNameOrEmail($login);
        $session = $user->authenticate($password);
        $db->commit();
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
    <title><?php echo _t('u', STRING_LOG_IN) ?></title>
    <link rel="stylesheet" type="text/css" href="static/css/forms.css"/>
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

    <div class="success" <?php echo $session ? "" : "hidden" ?>>
        <?php
        if ($session) {
            echo 'Opened session '.$session->getId().' with token '.$session->getToken();
        }
        ?>
    </div>

    <fieldset>
        <legend><?php echo _t('u', STRING_LOG_IN) ?></legend>
        <div>
            <label for="login"><?php echo _t('u', STRING_USERNAME_OR_EMAIL) ?>:</label>
            <input type="text" name="login" id="login" required
                   placeholder="<?php echo _t('l', STRING_USERNAME_OR_EMAIL) ?>"
                   value="<?php echo $login ?>">

        </div>
        <div>
            <label for="password"><?php echo _t('u', STRING_PASSWORD) ?>:</label>
            <input type="password" name="password" id="password" required
                   placeholder="<?php echo _t('l', STRING_PASSWORD) ?>"
                   minlength="<?php echo CFG_PASSWORD_MIN_LEN ?>" maxlength="<?php echo CFG_PASSWORD_MAX_LEN ?>">

        </div>
    </fieldset>

    <div>
        <input type="submit" value="<?php echo _t('u', STRING_LOG_IN) ?>">
    </div>
</form>
</body>
</html>
