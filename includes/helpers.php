<?php
// Shared helpers for all API endpoints
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/env.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => false, // set true in production with HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// CORS headers for local dev
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function sendResponse(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function sendError(string $message, int $code = 400): void {
    sendResponse(['success' => false, 'error' => $message], $code);
}

function requireAuth(): array {
    if (empty($_SESSION['user_id'])) {
        sendError('Unauthorized. Please log in.', 401);
    }
    return ['id' => $_SESSION['user_id'], 'email' => $_SESSION['user_email'], 'full_name' => $_SESSION['user_name']];
}

function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

function getRequestBody(): array {
    $raw = file_get_contents('php://input');
    if (empty($raw)) return $_POST;
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $_POST;
}

function logActivity(int $userId, string $actionType, string $description): void {
    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $actionType, $description]);
    } catch (Exception $e) {
        // Silently fail - logging should not break the main flow
    }
}
