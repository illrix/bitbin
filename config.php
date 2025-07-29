<?php
// Pastebin Clone Configuration

// Database configuration
define('DB_PATH', __DIR__ . '/data.db');

// Application settings
define('SITE_NAME', 'bitbin');
define('BASE_URL', 'http://localhost');
define('MAX_PASTE_SIZE', 1024 * 1024); // 1MB max paste size

// Expiration options (in seconds)
define('EXPIRATION_OPTIONS', [
    'never' => 0,
    '10min' => 600,
    '1hour' => 3600,
    '1day' => 86400,
    '1week' => 604800,
    '1month' => 2592000
]);

// Supported languages for syntax highlighting
define('LANGUAGES', [
    'text' => 'Plain Text',
    'php' => 'PHP',
    'javascript' => 'JavaScript',
    'python' => 'Python',
    'java' => 'Java',
    'c' => 'C',
    'cpp' => 'C++',
    'html' => 'HTML',
    'css' => 'CSS',
    'sql' => 'SQL',
    'json' => 'JSON',
    'xml' => 'XML',
    'bash' => 'Bash'
]);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

