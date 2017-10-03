<?php
require_once dirname(__FILE__).'/../../lib/api.php';

$req_body = file_get_contents('php://input');
$request = json_decode($req_body, true);

try {
    $user = User::create($request['username'], $request['email'], $request['password']);

    http_response_code(200);
    header("Content-Type: application/json");
    echo json_encode(["id" => $user->getId()], JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
}
catch (APIException $e) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $error_response = [
        "error_code" => $e->getCode(),
        "message" => $e->getMessage(),
        "ip" => $ip,
    ];

    if ($ip === "127.0.0.1" || substr($ip, 0, strlen("10.17.0.")) === "10.17.0.") {
        $error_response['trace'] = $e->getTraceAsString();
        $cause = $e->getPrevious();
        if ($cause) {
            $error_response['cause'] = [
                "trace" => $cause->getTraceAsString(),
                "message" => $cause->getMessage()
            ];
            if ($cause instanceof PDOException) {
                $error_response['cause']['errorInfo'] = $cause->errorInfo;
            }
        }
    }

    http_response_code($e->getRecommendedHttpStatus());
    header("Content-Type: application/json");
    echo json_encode($error_response);
}
