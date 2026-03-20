<?php
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

$body = getRequestBody();

$email    = filter_var(trim($body['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = $body['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError('Please enter a valid email address.');
}
if (empty($password)) {
    sendError('Password is required.');
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT id, full_name, email, password FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    sendError('Invalid email or password.', 401);
}

// Set session
$_SESSION['user_id']    = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name']  = $user['full_name'];

logActivity($user['id'], 'login', 'User logged in successfully.');

sendResponse([
    'success'   => true,
    'message'   => 'Welcome back, ' . $user['full_name'] . '!',
    'user'      => [
        'id'        => $user['id'],
        'full_name' => $user['full_name'],
        'email'     => $user['email'],
    ]
]);
