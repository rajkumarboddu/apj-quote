<?php

require_once("modules/core.php");

if(!Auth::isLoggedIn()) {
    $resp = [
        'status' => 401,
        'message' => 'Unauthorized'
    ];
    echo json_encode($resp);
    exit();
}

require_once("modules/api.php");

if(isset($_REQUEST["api_name"])) {
    Api::execute($_REQUEST["api_name"]);
} else {
    $resp = [
        'status' => 400,
        'message' => 'api name is required'
    ];
    echo json_encode($resp);
    exit();
}

?>