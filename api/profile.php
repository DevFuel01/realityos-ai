<?php
require_once __DIR__ . '/../includes/helpers.php';

$user = requireAuth();
$pdo  = Database::getConnection();

$stmt = $pdo->prepare("
    SELECT 
        u.id, u.full_name, u.email, u.created_at,
        COUNT(s.id) AS total_simulations,
        SUM(CASE WHEN s.risk_tolerance = 'High' THEN 1 ELSE 0 END) AS high_risk_decisions,
        SUM(CASE WHEN s.recommended_option IS NOT NULL THEN 1 ELSE 0 END) AS recommended_decisions
    FROM users u
    LEFT JOIN simulations s ON s.user_id = u.id
    WHERE u.id = ?
    GROUP BY u.id
");
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();

if (!$profile) {
    sendError('User not found.', 404);
}

sendResponse([
    'success' => true,
    'profile' => $profile
]);
