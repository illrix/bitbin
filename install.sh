#!/bin/bash

# PasteBin Clone Installation Script
# This script helps set up the pastebin clone on your server

echo "🚀 PasteBin Clone Installation Script"
echo "======================================"

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.1 or higher first."
    echo "   Ubuntu/Debian: sudo apt install php php-sqlite3"
    echo "   CentOS/RHEL: sudo yum install php php-pdo"
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;" 2>/dev/null)
echo "✅ PHP version: $PHP_VERSION"

# Check if SQLite extension is available
if ! php -m | grep -q sqlite3; then
    echo "❌ SQLite3 extension is not available. Please install php-sqlite3."
    exit 1
fi

echo "✅ SQLite3 extension is available"

# Set proper permissions
echo "🔧 Setting up permissions..."
chmod 755 .
chmod 644 *.php *.css *.sql *.md
chmod 755 install.sh

# Create database if it doesn't exist
if [ ! -f "pastebin.db" ]; then
    echo "🗄️  Creating database..."
    touch pastebin.db
    chmod 666 pastebin.db
    echo "✅ Database created"
else
    echo "✅ Database already exists"
fi

# Test database connection
echo "🧪 Testing database connection..."
php -r "
try {
    \$pdo = new PDO('sqlite:pastebin.db');
    echo '✅ Database connection successful\n';
} catch (Exception \$e) {
    echo '❌ Database connection failed: ' . \$e->getMessage() . '\n';
    exit(1);
}
"

echo ""
echo "🎉 Installation completed successfully!"
echo ""
echo "📋 Next steps:"
echo "   1. Start the development server: php -S localhost:8000"
echo "   2. Open your browser and go to: http://localhost:8000"
echo "   3. For production, copy files to your web server directory"
echo ""
echo "📖 For more information, see README.md"
echo ""
echo "🌟 Enjoy your new PasteBin clone!"

