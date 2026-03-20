<?php
require_once __DIR__ . '/../includes/helpers.php';

$user = requireAuth();
$pdo  = Database::getConnection();

$category = sanitize($_GET['category'] ?? '');
$limit    = min(50, max(1, (int)($_GET['limit'] ?? 20)));
$offset   = max(0, (int)($_GET['offset'] ?? 0));

if ($category) {
    $stmt = $pdo->prepare("
        SELECT id, decision_title, category, recommended_option, confidence_score, time_horizon, risk_tolerance, created_at
        FROM simulations WHERE user_id = ? AND category = ?
        ORDER BY created_at DESC LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user['id'], $category, $limit, $offset]);
} else {
    $stmt = $pdo->prepare("
        SELECT id, decision_title, category, recommended_option, confidence_score, time_horizon, risk_tolerance, created_at
        FROM simulations WHERE user_id = ?
        ORDER BY created_at DESC LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user['id'], $limit, $offset]);
}

$simulations = $stmt->fetchAll();

// Stats
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN risk_tolerance = 'High' THEN 1 ELSE 0 END) as high_risk,
        SUM(CASE WHEN recommended_option IS NOT NULL AND recommended_option != '' THEN 1 ELSE 0 END) as recommended
    FROM simulations WHERE user_id = ?
");
$statsStmt->execute([$user['id']]);
$stats = $statsStmt->fetch();

sendResponse([
    'success'     => true,
    'simulations' => $simulations,
    'stats'       => $stats,
    'total'       => (int)$stats['total']
]);
