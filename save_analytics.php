<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['test_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

try {
    $db = getDb();
    $stmt = $db->prepare("INSERT INTO analytics (test_id, device_info, score_percent, correct_count, total_questions) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $input['test_id'],
        $input['device_info'] ?? 'Unknown Device',
        intval($input['score_percent']),
        intval($input['correct_count']),
        intval($input['total_questions'])
    ]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'message' => $e->getMessage()]);
}
