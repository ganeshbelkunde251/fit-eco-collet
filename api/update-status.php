<?php
/**
 * API Endpoint: Admin Update Request Status
 * EcoCollect Platform
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/auth.php';

if (!is_admin()) {
    http_response_code(403);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Unauthorized access. Administrator privileges required.'
    ]);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

$requestId = sanitize_input($data['request_id'] ?? '');
$newStatus = sanitize_input($data['status'] ?? '');

$allowedStatuses = ['Pending', 'Assigned', 'Scheduled', 'Picked Up', 'Completed', 'Cancelled'];

if (empty($requestId) || !in_array($newStatus, $allowedStatuses)) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid request ID or status value provided.'
    ]);
    exit;
}

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("UPDATE pickup_requests SET status = :status WHERE request_id = :req_id");
    $stmt->execute([':status' => $newStatus, ':req_id' => $requestId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status'  => 'success',
            'message' => "Request {$requestId} status updated to {$newStatus}."
        ]);
    } else {
        echo json_encode([
            'status'  => 'success',
            'message' => "Request status was already set to {$newStatus}."
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Failed to update request status: ' . $e->getMessage()
    ]);
}
