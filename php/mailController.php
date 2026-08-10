<?php

header('Content-Type: application/json');

require_once("mailTrigger.php");

$response = [
    "success" => false,
    "message" => ""
];

if ($_SERVER["REQUEST_METHOD"] != "POST") {

    $response["message"] = "Invalid request.";

    echo json_encode($response);

    exit;
}

$required = [
    'first_name',
    'contact_number',
    'location',
    'requirements'
];

foreach ($required as $field) {

    if (!isset($_POST[$field]) || trim($_POST[$field]) == "") {

        $response["message"] = ucfirst(str_replace("_"," ",$field))." is required.";

        echo json_encode($response);

        exit;
    }
}

$mail = new sndMail();

echo json_encode($mail->contactEnquiry($_POST));