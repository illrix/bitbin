<?php
require_once 'config.php';
require_once 'database.php';

// Initialize database
$db = new Database();

// Get paste ID from URL
$pasteId = $_GET['id'] ?? '';

if (empty($pasteId)) {
    http_response_code(404);
    echo 'Paste not found';
    exit;
}

// Get paste data
$paste = $db->getPaste($pasteId);

if (!$paste) {
    http_response_code(404);
    echo 'Paste not found or has expired';
    exit;
}

// Set appropriate headers for plain text
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: inline; filename="' . $paste['paste_id'] . '.txt"');

// Output the raw content
echo $paste['content'];
?>

