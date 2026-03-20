<?php
require_once __DIR__ . '/../includes/helpers.php';

$user = requireAuth();
$id   = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    sendError('Invalid simulation ID.');
}

$pdo  = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM simulations WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user['id']]);
$simulation = $stmt->fetch();

if (!$simulation) {
    sendError('Simulation not found.', 404);
}

// Parse AI JSON
if (!empty($simulation['ai_response_json'])) {
    $simulation['ai_analysis'] = json_decode($simulation['ai_response_json'], true);
    unset($simulation['ai_response_json']);
}

sendResponse(['success' => true, 'simulation' => $simulation]);
