<?php
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

$body = getRequestBody();

$fullName = sanitize($body['full_name'] ?? '');
$email    = filter_var(trim($body['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = $body['password'] ?? '';

// Validation
if (empty($fullName) || strlen($fullName) < 2) {
    sendError('Full name must be at least 2 characters.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError('Please enter a valid email address.');
}
if (strlen($password) < 6) {
    sendError('Password must be at least 6 characters.');
}

$pdo = Database::getConnection();

// Check if email already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    sendError('An account with this email already exists.');
}

// Hash password and insert
$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $pdo->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
$stmt->execute([$fullName, $email, $hashedPassword]);
$userId = $pdo->lastInsertId();

// Log activity
logActivity($userId, 'register', 'New user registered.');

sendResponse([
    'success' => true,
    'message' => 'Account created successfully! Welcome to RealityOS AI.',
    'user_id' => $userId
], 201);
