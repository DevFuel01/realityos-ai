<?php
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

if (!empty($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], 'logout', 'User logged out.');
}

session_unset();
session_destroy();

sendResponse(['success' => true, 'message' => 'Logged out successfully.']);
