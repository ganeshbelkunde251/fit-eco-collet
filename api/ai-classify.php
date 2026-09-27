<?php
/**
 * API Endpoint: AI Waste Classifier (Gemini Integration Proxy)
 * EcoCollect Management Platform
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/auth.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Unauthorized access. Please sign in.'
    ]);
    exit;
}

// Read raw JSON input
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

$description = trim($data['description'] ?? '');

if (empty($description)) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Waste description is required.'
    ]);
    exit;
}

$apiKey = env('GEMINI_API_KEY');

if (empty($apiKey) || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
    // Graceful fallback response if API key is not configured locally
    echo json_encode([
        'status'  => 'fallback',
        'message' => 'AI assistant is temporarily unavailable. You can still manually select a waste category and submit your request.',
        'result'  => [
            'category'    => 'Other',
            'reason'      => 'Gemini API Key is not configured in the server .env environment.',
            'action'      => 'Please select a waste category manually from the request form options.',
            'safety_note' => 'Always handle unknown or hazardous waste with protective gloves.'
        ]
    ]);
    exit;
}

try {
    // Gemini REST API endpoint
    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . urlencode($apiKey);

    $promptText = "You are an expert waste classification and recycling system. " .
                  "Analyze this user waste item description: \"" . addslashes($description) . "\". " .
                  "Determine the best matching category from this strict list: ['Dry Waste', 'Wet Waste', 'Plastic', 'Paper', 'Glass', 'Metal', 'E-Waste', 'Hazardous Waste', 'Other']. " .
                  "Respond strictly with valid JSON only in the following format (do not add conversational prose or markdown formatting outside JSON):\n" .
                  "{\n" .
                  "  \"category\": \"<Category Name>\",\n" .
                  "  \"reason\": \"<1-2 sentence explanation of why it fits this category>\",\n" .
                  "  \"action\": \"<1 sentence recommended pickup or disposal step>\",\n" .
                  "  \"safety_note\": \"<1 sentence safety handling instruction>\"\n" .
                  "}";

    $postData = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $promptText]
                ]
            ]
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // Enable SSL verification by default
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr || $httpCode !== 200 || !$response) {
        throw new Exception("Gemini API HTTP Error ({$httpCode}): " . ($curlErr ?: 'Empty response'));
    }

    $resData = json_decode($response, true);
    $responseText = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '';

    // Clean Markdown json fences if present
    $cleanJson = preg_replace('/^```json\s*/i', '', trim($responseText));
    $cleanJson = preg_replace('/```$/', '', trim($cleanJson));

    $parsedResult = json_decode(trim($cleanJson), true);

    if (!is_array($parsedResult) || !isset($parsedResult['category'])) {
        throw new Exception("Failed to parse Gemini output into expected JSON schema.");
    }

    echo json_encode([
        'status' => 'success',
        'result' => [
            'category'    => $parsedResult['category'] ?? 'Other',
            'reason'      => $parsedResult['reason'] ?? 'Classified based on description.',
            'action'      => $parsedResult['action'] ?? 'Schedule a pickup request.',
            'safety_note' => $parsedResult['safety_note'] ?? 'Handle with standard household care.'
        ]
    ]);

} catch (Exception $e) {
    error_log("AI Classify API Exception: " . $e->getMessage());

    // Graceful error fallback to avoid breaking the application UI
    echo json_encode([
        'status'  => 'fallback',
        'message' => 'AI assistant is temporarily unavailable. You can still manually select a waste category and submit your request.',
        'result'  => [
            'category'    => 'Other',
            'reason'      => 'Gemini API call failed: ' . $e->getMessage(),
            'action'      => 'Please select a waste category manually from the request form.',
            'safety_note' => 'Handle sharp or hazardous items with care.'
        ]
    ]);
}
