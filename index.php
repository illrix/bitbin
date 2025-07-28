<?php
require_once 'config.php';
require_once 'database.php';

// Initialize database
$db = new Database();

// Clean up expired pastes
$db->cleanupExpiredPastes();

// Get recent pastes for sidebar
$recentPastes = $db->getRecentPastes(10);

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_paste'])) {
    $title = trim($_POST['title']) ?: 'Untitled';
    $content = $_POST['content'];
    $language = $_POST['language'];
    $expiration = (int)$_POST['expiration'];
    
    // Validate input
    if (empty($content)) {
        $message = 'Content cannot be empty!';
        $messageType = 'error';
    } elseif (strlen($content) > MAX_PASTE_SIZE) {
        $message = 'Content is too large! Maximum size is ' . (MAX_PASTE_SIZE / 1024) . 'KB.';
        $messageType = 'error';
    } else {
        // Create paste
        $pasteId = $db->generateUniqueId();
        
        if ($db->createPaste($pasteId, $title, $content, $language, $expiration)) {
            header('Location: view.php?id=' . $pasteId);
            exit;
        } else {
            $message = 'Failed to create paste. Please try again.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><a href="index.php"><?php echo SITE_NAME; ?></a></h1>
            <p>Share your code and text snippets easily</p>
        </header>

        <main>
            <div class="content-wrapper">
                <div class="main-content">
                    <?php if ($message): ?>
                        <div class="message <?php echo $messageType; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="paste-form">
                        <div class="form-group">
                            <label for="title">Title (optional):</label>
                            <input type="text" id="title" name="title" placeholder="Enter a title for your paste" 
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="content">Content:</label>
                            <textarea id="content" name="content" placeholder="Paste your code or text here..." required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="language">Language:</label>
                                <select id="language" name="language">
                                    <?php foreach (LANGUAGES as $code => $name): ?>
                                        <option value="<?php echo $code; ?>" 
                                                <?php echo (isset($_POST['language']) && $_POST['language'] === $code) ? 'selected' : ''; ?>>
                                            <?php echo $name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="expiration">Expiration:</label>
                                <select id="expiration" name="expiration">
                                    <option value="0">Never</option>
                                    <option value="600">10 minutes</option>
                                    <option value="3600">1 hour</option>
                                    <option value="86400">1 day</option>
                                    <option value="604800">1 week</option>
                                    <option value="2592000">1 month</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" name="create_paste" class="btn btn-primary">Create Paste</button>
                    </form>
                </div>

                <aside class="sidebar">
                    <h3>Recent Pastes</h3>
                    <?php if (empty($recentPastes)): ?>
                        <p class="no-pastes">No pastes yet. Be the first to create one!</p>
                    <?php else: ?>
                        <ul class="recent-pastes">
                            <?php foreach ($recentPastes as $paste): ?>
                                <li>
                                    <a href="view.php?id=<?php echo $paste['paste_id']; ?>">
                                        <div class="paste-title"><?php echo htmlspecialchars($paste['title']); ?></div>
                                        <div class="paste-meta">
                                            <span class="language"><?php echo LANGUAGES[$paste['language']] ?? $paste['language']; ?></span>
                                            <span class="views"><?php echo $paste['views']; ?> views</span>
                                            <span class="date"><?php echo date('M j, Y', strtotime($paste['created_at'])); ?></span>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </aside>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 <?php echo SITE_NAME; ?>. Simple pastebin clone built with PHP.</p>
        </footer>
    </div>
</body>
</html>

