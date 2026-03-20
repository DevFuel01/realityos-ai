<?php
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

$user = requireAuth();
$body = getRequestBody();

$required = ['decision_title', 'category', 'situation_description', 'option_a_title', 'option_a_description', 'option_b_title', 'option_b_description', 'ai_response_json'];
foreach ($required as $field) {
    if (empty($body[$field])) {
        sendError('Field "' . $field . '" is required.');
    }
}

// Validate AI response JSON
$aiJson = $body['ai_response_json'];
if (is_array($aiJson)) {
    $aiJson = json_encode($aiJson);
}
$decoded = json_decode($aiJson, true);
if (!is_array($decoded)) {
    sendError('Invalid AI response data.');
}

$recommendedOption = sanitize($decoded['recommended_option'] ?? '');
$confidenceScore   = min(100, max(0, (int)($decoded['confidence_score'] ?? 0)));

$pdo  = Database::getConnection();
$stmt = $pdo->prepare("
    INSERT INTO simulations 
    (user_id, decision_title, category, situation_description, 
     option_a_title, option_a_description, option_b_title, option_b_description,
     user_goal, time_horizon, risk_tolerance, ai_response_json, recommended_option, confidence_score)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $user['id'],
    sanitize($body['decision_title']),
    sanitize($body['category']),
    sanitize($body['situation_description']),
    sanitize($body['option_a_title']),
    sanitize($body['option_a_description']),
    sanitize($body['option_b_title']),
    sanitize($body['option_b_description']),
    sanitize($body['user_goal'] ?? ''),
    sanitize($body['time_horizon'] ?? 'Mid-term'),
    sanitize($body['risk_tolerance'] ?? 'Medium'),
    $aiJson,
    $recommendedOption,
    $confidenceScore,
]);

$simId = $pdo->lastInsertId();
logActivity($user['id'], 'save_simulation', 'Saved simulation: ' . sanitize($body['decision_title']));

sendResponse([
    'success'       => true,
    'message'       => 'Simulation saved successfully.',
    'simulation_id' => $simId
], 201);
