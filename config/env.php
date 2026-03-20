<?php
// RealityOS AI - Environment Configuration
// IMPORTANT: Never commit this file to version control with real keys

define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY');
define('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent');
define('GEMINI_API_URL', GEMINI_API_ENDPOINT); // alias for compatibility

define('APP_NAME', 'RealityOS AI');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/RealityOSAI');

define('SESSION_LIFETIME', 86400); // 24 hours
define('JWT_SECRET', 'realityos_super_secret_jwt_key_2026_change_in_production');

// DB Config (used in database.php)
define('DB_HOST', 'localhost');
define('DB_NAME', 'realityos_ai');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
