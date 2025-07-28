<?php
require_once 'config.php';
require_once 'database.php';

// Initialize database
$db = new Database();

// Get paste ID from URL
$pasteId = $_GET['id'] ?? '';

if (empty($pasteId)) {
    header('Location: index.php');
    exit;
}

// Get paste data
$paste = $db->getPaste($pasteId);

if (!$paste) {
    $error = 'Paste not found or has expired.';
}

// Get recent pastes for sidebar
$recentPastes = $db->getRecentPastes(10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($paste) ? htmlspecialchars($paste['title']) . ' - ' . SITE_NAME : 'Paste Not Found - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
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
                    <?php if (isset($error)): ?>
                        <div class="message error">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                        <p><a href="index.php" class="btn btn-primary">Create New Paste</a></p>
                    <?php else: ?>
                        <div class="paste-header">
                            <h2><?php echo htmlspecialchars($paste['title']); ?></h2>
                            <div class="paste-meta">
                                <span class="language">
                                    <strong>Language:</strong> <?php echo LANGUAGES[$paste['language']] ?? $paste['language']; ?>
                                </span>
                                <span class="created">
                                    <strong>Created:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($paste['created_at'])); ?>
                                </span>
                                <span class="views">
                                    <strong>Views:</strong> <?php echo $paste['views']; ?>
                                </span>
                                <?php if ($paste['expiration']): ?>
                                    <span class="expiration">
                                        <strong>Expires:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($paste['expiration'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="paste-actions">
                            <button onclick="copyToClipboard()" class="btn btn-secondary">Copy to Clipboard</button>
                            <a href="raw.php?id=<?php echo $paste['paste_id']; ?>" class="btn btn-secondary" target="_blank">View Raw</a>
                            <a href="index.php" class="btn btn-primary">Create New Paste</a>
                        </div>

                        <div class="paste-content">
                            <pre><code class="language-<?php echo htmlspecialchars($paste['language']); ?>" id="paste-code"><?php echo htmlspecialchars($paste['content']); ?></code></pre>
                        </div>
                    <?php endif; ?>
                </div>

                <aside class="sidebar">
                    <h3>Recent Pastes</h3>
                    <?php if (empty($recentPastes)): ?>
                        <p class="no-pastes">No pastes yet.</p>
                    <?php else: ?>
                        <ul class="recent-pastes">
                            <?php foreach ($recentPastes as $recentPaste): ?>
                                <li>
                                    <a href="view.php?id=<?php echo $recentPaste['paste_id']; ?>" 
                                       <?php echo ($recentPaste['paste_id'] === $pasteId) ? 'class="current"' : ''; ?>>
                                        <div class="paste-title"><?php echo htmlspecialchars($recentPaste['title']); ?></div>
                                        <div class="paste-meta">
                                            <span class="language"><?php echo LANGUAGES[$recentPaste['language']] ?? $recentPaste['language']; ?></span>
                                            <span class="views"><?php echo $recentPaste['views']; ?> views</span>
                                            <span class="date"><?php echo date('M j, Y', strtotime($recentPaste['created_at'])); ?></span>
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

    <script>
        function copyToClipboard() {
            const codeElement = document.getElementById('paste-code');
            const text = codeElement.textContent;
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopyMessage('Copied to clipboard!');
                }).catch(function() {
                    fallbackCopyToClipboard(text);
                });
            } else {
                fallbackCopyToClipboard(text);
            }
        }

        function fallbackCopyToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                showCopyMessage('Copied to clipboard!');
            } catch (err) {
                showCopyMessage('Failed to copy to clipboard');
            }
            
            document.body.removeChild(textArea);
        }

        function showCopyMessage(message) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'copy-message';
            messageDiv.textContent = message;
            document.body.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 2000);
        }
    </script>
</body>
</html>

