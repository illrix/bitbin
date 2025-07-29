-- Database Schema
CREATE TABLE IF NOT EXISTS pastes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paste_id VARCHAR(10) UNIQUE NOT NULL,
    title VARCHAR(255) DEFAULT 'Untitled',
    content TEXT NOT NULL,
    language VARCHAR(50) DEFAULT 'text',
    expiration DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    views INTEGER DEFAULT 0
);

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_paste_id ON pastes(paste_id);
CREATE INDEX IF NOT EXISTS idx_created_at ON pastes(created_at);

