<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$GEMINI_API_KEY = "AIzaSyDqTzrEiXBhocGLzMXC3Smlo_WExn8C8T4";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['prompt']) || empty($input['prompt'])) {
        http_response_code(400);
        echo json_encode(["error" => "Prompt is required"]);
        exit;
    }

    $prompt = $input['prompt'];
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$GEMINI_API_KEY";

    $body = json_encode([
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ]
    ]);

    $headers = [
        "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($http_code === 200 && isset($result["candidates"][0]["content"]["parts"][0]["text"])) {
        $reply = $result["candidates"][0]["content"]["parts"][0]["text"];
        echo json_encode(["response" => $reply]);
    } else {
        http_response_code($http_code);
        echo json_encode(["error" => $result]);
    }

} else {
    http_response_code(405);
    echo json_encode(["error" => "POST method only"]);
}
