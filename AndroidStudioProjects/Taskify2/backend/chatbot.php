<?php
declare(strict_types=1);

// CORS headers for web/mobile clients
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle CORS preflight quickly and without body
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit; // No body for preflight
}

// Always return XML for non-preflight requests
header('Content-Type: application/xml; charset=UTF-8');

// Never expose PHP errors
ini_set('display_errors', '0');
error_reporting(E_ALL);

/**
 * Send a well-formed XML response and terminate.
 */
function sendXmlResponse(string $status, string $message, int $httpStatusCode = 200): void
{
    $document = new DOMDocument('1.0', 'UTF-8');
    $document->formatOutput = false;

    $root = $document->createElement('response');
    $document->appendChild($root);

    $statusElement = $document->createElement('status');
    $statusElement->appendChild($document->createTextNode($status));

    $messageElement = $document->createElement('message');
    // DOMDocument::createTextNode safely escapes XML characters
    $messageElement->appendChild($document->createTextNode($message));

    $root->appendChild($statusElement);
    $root->appendChild($messageElement);

    http_response_code($httpStatusCode);
    echo $document->saveXML();
    exit;
}

/**
 * Generic error handler to avoid leaking internal details.
 */
set_exception_handler(function (Throwable $e): void {
    sendXmlResponse('error', 'Invalid request', 400);
});

set_error_handler(function (int $severity, string $message) {
    // Convert all PHP errors/warnings/notices into a safe XML error response
    sendXmlResponse('error', 'Invalid request', 400);
});

// Only POST is allowed for actual requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendXmlResponse('error', 'Method not allowed', 405);
}

// Ensure we only accept JSON body
$rawBody = file_get_contents('php://input');
if ($rawBody === false || trim($rawBody) === '') {
    sendXmlResponse('error', 'Invalid request', 400);
}

// Decode JSON safely
$data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);

// Validate input shape
if (!isset($data['message']) || !is_string($data['message'])) {
    sendXmlResponse('error', 'Invalid request', 400);
}

$userMessage = trim($data['message']);
if ($userMessage === '') {
    sendXmlResponse('error', 'Invalid request', 400);
}

// Optional guardrails
if (mb_strlen($userMessage) > 2000) {
    sendXmlResponse('error', 'Message too long', 413);
}

// Basic stubbed AI logic (modular for future expansion)
function generateBotReply(string $message): string
{
    $lower = mb_strtolower($message);
    if (preg_match('/^(hi|hello|hey)\b/i', $message)) {
        return 'Hello! How can I help you today?';
    }
    if (strpos($lower, 'help') !== false) {
        return 'Sure — tell me a bit more about what you need help with.';
    }
    if (strpos($lower, 'task') !== false) {
        return 'You can create, update, or complete tasks. What would you like to do?';
    }
    return 'Got it. I\'m here to help — please provide more details.';
}

$botReply = generateBotReply($userMessage);

sendXmlResponse('success', $botReply, 200);


