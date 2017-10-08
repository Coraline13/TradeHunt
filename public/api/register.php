<?php
require_once dirname(__FILE__) . '/../../lib/api.php';

check_method(["POST"]);

$req_body = file_get_contents('php://input');
$request = json_decode($req_body, true);

try {
    $username = $request['username'];
    $email = $request['email'];
    $password = $request['password'];
    $user = User::create($username, $email, $password);

    http_response_code(200);
    header("Content-Type: application/json");
    echo json_encode(["id" => $user->getId()], JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
} catch (APIException $e) {
    write_error_response_json($e);
    log_warning("register failed for ${request['username']} <${request['email']}>: " . $e->getMessage());
    log_exception($e, LOG_LEVEL_DEBUG);
}
