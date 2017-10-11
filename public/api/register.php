<?php
require_once dirname(__FILE__) . '/../../lib/api.php';

check_method(["POST"]);

$req_body = file_get_contents('php://input');
$req = json_decode($req_body, true);

$first_name = validate_array_value($req, 'first_name', [validator_string_length(get_string(STRING_FIRST_NAME), 1, CFG_NAME_MAX_LEN)]);
$last_name = validate_array_value($req, 'last_name', [validator_string_length(get_string(STRING_LAST_NAME), 1, CFG_NAME_MAX_LEN)]);
$tel = require_array_value($req, 'tel', false);
$tel = validate_value($tel, 'tel', [validator_phone_number()]);

$username = validate_array_value($req, 'username', [
    validator_string_length(get_string(STRING_USERNAME), CFG_USERNAME_MIN_LEN, CFG_USERNAME_MAX_LEN),
    validator_regex(get_string(STRING_USERNAME), '/'.CFG_USERNAME_REGEX.'/')
]);
$email = validate_array_value($req, 'email', [validator_email()]);
$password = validate_array_value($req, 'password', [validator_string_length(get_string(STRING_PASSWORD), CFG_PASSWORD_MIN_LEN, CFG_PASSWORD_MAX_LEN)]);

try {
    $db->beginTransaction();
    $location = Location::getById(require_array_value($req, 'location_id', false));
    $profile = Profile::create($location, $first_name, $last_name, $tel);
    $user = User::create($username, $email, $password, $profile);
    $db->commit();

    http_response_code(200);
    header("Content-Type: application/json");
    echo json_encode(["id" => $user->getId()], JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
} catch (UserException $e) {
    write_error_response_json($e);
    log_warning("register failed for ${req['username']} <${req['email']}>: " . $e->getMessage());
    log_exception($e, LOG_LEVEL_DEBUG);
}
