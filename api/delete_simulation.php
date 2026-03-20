<?php
require_once __DIR__ . '/../includes/helpers.php';

$user = requireAuth();
$id   = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_GET['id']))) {
    sendError('Method not allowed', 405);
}

if ($id <= 0) {
    sendError('Invalid simulation ID.');
}

$pdo  = Database::getConnection();
// Verify ownership
$stmt = $pdo->prepare("SELECT id, decision_title FROM simulations WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user['id']]);
$sim  = $stmt->fetch();

if (!$sim) {
    sendError('Simulation not found or access denied.', 404);
}

$delStmt = $pdo->prepare("DELETE FROM simulations WHERE id = ? AND user_id = ?");
$delStmt->execute([$id, $user['id']]);

logActivity($user['id'], 'delete_simulation', 'Deleted simulation: ' . $sim['decision_title']);

sendResponse(['success' => true, 'message' => 'Simulation deleted successfully.']);
