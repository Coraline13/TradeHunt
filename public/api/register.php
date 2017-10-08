<?php
require_once dirname(__FILE__) . '/../../lib/api.php';

check_method(["POST"]);

$req_body = file_get_contents('php://input');
$req = json_decode($req_body, true);

try {
    $location = Location::getById($req['location_id']);
    $profile = Profile::create($location, $req['first_name'], $req['last_name'], $req['tel']);
    $user = User::create($req['username'], $req['email'], $req['password'], $profile);

    http_response_code(200);
    header("Content-Type: application/json");
    echo json_encode(["id" => $user->getId()], JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
} catch (UserException $e) {
    write_error_response_json($e);
    log_warning("register failed for ${req['username']} <${req['email']}>: " . $e->getMessage());
    log_exception($e, LOG_LEVEL_DEBUG);
}
