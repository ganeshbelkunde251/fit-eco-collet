<?php
/**
 * API Endpoint: Search Pickup Requests
 * EcoCollect Platform
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/auth.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user = current_user();
$search = sanitize_input($_GET['query'] ?? '');
$status = sanitize_input($_GET['status'] ?? '');

try {
    $pdo = get_db_connection();
    
    if (is_admin()) {
        $sql = "SELECT r.*, u.name as user_name FROM pickup_requests r JOIN users u ON r.user_id = u.id WHERE 1=1";
        $params = [];
    } else {
        $sql = "SELECT * FROM pickup_requests WHERE user_id = :uid";
        $params = [':uid' => $user['id']];
    }

    if (!empty($search)) {
        $sql .= " AND (request_id LIKE :q OR description LIKE :q OR city LIKE :q)";
        $params[':q'] = "%{$search}%";
    }

    if (!empty($status)) {
        $sql .= " AND status = :status";
        $params[':status'] = $status;
    }

    $sql .= " ORDER BY id DESC LIMIT 20";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    echo json_encode([
        'status'  => 'success',
        'count'   => count($results),
        'results' => $results
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database search failed: ' . $e->getMessage()
    ]);
}
