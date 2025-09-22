<?php
require_once __DIR__ . '/vendor/autoload.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ✅ Ensure .env loads
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$GEMINI_API_KEY = $_ENV['GEMINI_API_KEY'] ?? '';
if ($GEMINI_API_KEY === '') {
    http_response_code(500);
    echo json_encode(["error" => "Missing GEMINI_API_KEY"]);
    exit;
}

// ✅ Helper: make Gemini API call
function call_gemini($url, $body, $headers, &$http_code, &$curl_err) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $res = curl_exec($ch);
    $curl_err = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $res;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);

    // ✅ Invalid JSON
    if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid JSON"]);
        exit;
    }

    if (!isset($input['prompt']) || empty(trim($input['prompt']))) {
        http_response_code(400);
        echo json_encode(["error" => "Prompt is required"]);
        exit;
    }

    // ✅ Input hygiene
    $prompt = trim($input['prompt']);
    if (mb_strlen($prompt) > 4000) {
        http_response_code(413);
        echo json_encode(["error" => "Prompt too long"]);
        exit;
    }

    $body = json_encode([
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ]
    ]);

    $headers = ["Content-Type: application/json"];

    // ✅ Primary model
    $model = 'gemini-2.0-flash';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=$GEMINI_API_KEY";
    $response = call_gemini($url, $body, $headers, $http_code, $curl_err);

    // ✅ Auto-fallback if model not found/invalid
    if ($http_code === 404 || $http_code === 400) {
        $model = 'gemini-1.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=$GEMINI_API_KEY";
        $response = call_gemini($url, $body, $headers, $http_code, $curl_err);
    }

    if ($curl_err) {
        http_response_code(502);
        echo json_encode(["error" => "cURL error", "detail" => $curl_err]);
        exit;
    }

    $result = json_decode($response, true);

    if ($http_code === 200 && isset($result["candidates"][0]["content"]["parts"][0]["text"])) {
        echo json_encode(["response" => $result["candidates"][0]["content"]["parts"][0]["text"]]);
    } else {
        http_response_code($http_code ?: 500);
        echo json_encode(["error" => $result ?? $response]);
    }

} else {
    http_response_code(405);
    echo json_encode(["error" => "POST method only"]);
}
