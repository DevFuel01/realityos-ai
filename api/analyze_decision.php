<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../classes/GeminiService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

$user = requireAuth();
$body = getRequestBody();

// Validate required fields
$required = ['decision_title', 'category', 'situation_description', 'option_a_title', 'option_a_description', 'option_b_title', 'option_b_description'];
foreach ($required as $field) {
    if (empty($body[$field])) {
        sendError('Field "' . $field . '" is required.');
    }
}

$params = [
    'decision_title'       => sanitize($body['decision_title']),
    'category'             => sanitize($body['category']),
    'situation_description'=> sanitize($body['situation_description']),
    'option_a_title'       => sanitize($body['option_a_title']),
    'option_a_description' => sanitize($body['option_a_description']),
    'option_b_title'       => sanitize($body['option_b_title']),
    'option_b_description' => sanitize($body['option_b_description']),
    'user_goal'            => sanitize($body['user_goal'] ?? ''),
    'time_horizon'         => sanitize($body['time_horizon'] ?? 'Mid-term'),
    'risk_tolerance'       => sanitize($body['risk_tolerance'] ?? 'Medium'),
];

try {
    $gemini  = new GeminiService();
    $aiResult = $gemini->analyzeDecision($params);
} catch (Exception $e) {
    sendError('AI analysis failed: ' . $e->getMessage(), 500);
}

sendResponse([
    'success'  => true,
    'analysis' => $aiResult,
    'params'   => $params
]);
