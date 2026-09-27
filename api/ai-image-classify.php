<?php
/**
 * API Endpoint: AI Camera Waste Scanner (Gemini Multimodal Vision Proxy)
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

// Read raw JSON input or POST form file upload
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

$base64Data = '';
$mimeType = 'image/jpeg';

if (!empty($data['image'])) {
    $rawImage = $data['image'];
    if (preg_match('/^data:(image\/(jpeg|jpg|png|webp));base64,(.+)$/i', $rawImage, $matches)) {
        $mimeType = strtolower($matches[1]);
        if ($mimeType === 'image/jpg') $mimeType = 'image/jpeg';
        $base64Data = $matches[3];
    } else {
        $base64Data = $rawImage;
    }
} elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['image']['tmp_name'];
    $fileType = mime_content_type($fileTmp);
    if (in_array($fileType, ['image/jpeg', 'image/png', 'image/jpg'])) {
        $mimeType = $fileType === 'image/jpg' ? 'image/jpeg' : $fileType;
        $base64Data = base64_encode(file_get_contents($fileTmp));
    }
}

if (empty($base64Data)) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'No valid image data provided. Please capture or upload an image.'
    ]);
    exit;
}

$apiKey = env('GEMINI_API_KEY');

if (empty($apiKey) || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
    // Graceful fallback response if API key is not configured locally
    echo json_encode([
        'status'  => 'fallback',
        'message' => 'AI Vision assistant is temporarily unavailable. GEMINI_API_KEY is missing in .env.',
        'result'  => [
            'object_name'      => 'Unidentified Object (API Key Missing)',
            'category'         => 'Other',
            'waste_type'       => 'General Waste',
            'recyclable'       => 'No',
            'disposal_method'  => 'Please select a waste category manually from the pickup request form.',
            'safety_warning'   => 'Handle items with caution and check local disposal rules.',
            'confidence_notes' => 'Simulated offline response because GEMINI_API_KEY is not configured in .env.'
        ]
    ]);
    exit;
}

try {
    // Gemini REST API endpoint
    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . urlencode($apiKey);

    $promptText = "You are an expert waste classification, environmental, and recycling AI assistant. " .
                  "Examine the provided image of an object or waste item carefully.\n" .
                  "Respond strictly with valid JSON only in the following exact format without any codeblock markers or prose:\n" .
                  "{\n" .
                  "  \"object_name\": \"<Identified product or object name, e.g. Old Laptop, Plastic Water Bottle, Lithium Battery>\",\n" .
                  "  \"category\": \"<Must be EXACTLY one of: Wet Waste, Dry Waste, Plastic, Paper, Glass, Metal, E-Waste, Hazardous Waste, Other>\",\n" .
                  "  \"waste_type\": \"<Specific type name, e.g. Electronic Waste, Recyclable PET Plastic, Organic Food Waste>\",\n" .
                  "  \"recyclable\": \"<Yes or No>\",\n" .
                  "  \"disposal_method\": \"<1-2 sentence recommended disposal or recycling instruction>\",\n" .
                  "  \"safety_warning\": \"<1 sentence safety precaution or handling warning if applicable>\",\n" .
                  "  \"confidence_notes\": \"<1 sentence AI confidence remark or condition note>\"\n" .
                  "}";

    $postData = [
        'contents' => [
            [
                'parts' => [
                    [
                        'inlineData' => [
                            'mimeType' => $mimeType,
                            'data'     => $base64Data
                        ]
                    ],
                    [
                        'text' => $promptText
                    ]
                ]
            ]
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr || $httpCode !== 200 || !$response) {
        throw new Exception("Gemini Vision API Error ({$httpCode}): " . ($curlErr ?: 'Empty response received'));
    }

    $resData = json_decode($response, true);
    $responseText = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '';

    // Strip markdown codeblock backticks if present
    $cleanJson = preg_replace('/^```json\s*/i', '', trim($responseText));
    $cleanJson = preg_replace('/```$/', '', trim($cleanJson));

    $parsed = json_decode(trim($cleanJson), true);

    if (!is_array($parsed) || !isset($parsed['category'])) {
        throw new Exception("Could not parse AI response into structured waste classification output.");
    }

    // Ensure category is one of the supported categories
    $supportedCategories = [
        'Wet Waste', 'Dry Waste', 'Plastic', 'Paper', 'Glass', 'Metal', 'E-Waste', 'Hazardous Waste', 'Other'
    ];

    $validCategory = 'Other';
    foreach ($supportedCategories as $cat) {
        if (strcasecmp($cat, trim($parsed['category'])) === 0) {
            $validCategory = $cat;
            break;
        }
    }

    echo json_encode([
        'status' => 'success',
        'result' => [
            'object_name'      => $parsed['object_name'] ?? 'Scanned Item',
            'category'         => $validCategory,
            'waste_type'       => $parsed['waste_type'] ?? 'General Waste',
            'recyclable'       => (strcasecmp($parsed['recyclable'] ?? '', 'Yes') === 0) ? 'Yes' : 'No',
            'disposal_method'  => $parsed['disposal_method'] ?? 'Dispose according to local recycling regulations.',
            'safety_warning'   => $parsed['safety_warning'] ?? 'Handle with standard household care.',
            'confidence_notes' => $parsed['confidence_notes'] ?? 'Item analyzed based on visual camera scan.'
        ]
    ]);

} catch (Exception $e) {
    error_log("AI Image Classify Error: " . $e->getMessage());

    echo json_encode([
        'status'  => 'fallback',
        'message' => 'AI Vision scanner is temporarily unavailable: ' . $e->getMessage(),
        'result'  => [
            'object_name'      => 'Unidentified Waste Item',
            'category'         => 'Other',
            'waste_type'       => 'Uncategorized',
            'recyclable'       => 'No',
            'disposal_method'  => 'Please select a waste category manually on the pickup request form.',
            'safety_warning'   => 'Handle items carefully.',
            'confidence_notes' => 'AI Vision analysis encountered an issue. Manual category selection is available.'
        ]
    ]);
}
