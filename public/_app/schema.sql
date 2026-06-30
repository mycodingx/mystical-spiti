-- ============================================================
-- Mystical Expedition - Lead Capture Database Schema
-- ============================================================
-- SQLite database: data/leads.sqlite
-- Auto-migrated on first run by src/Database.php
-- ============================================================

CREATE TABLE IF NOT EXISTS leads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL,
    city        TEXT NOT NULL,
    email       TEXT NOT NULL,
    phone       TEXT NOT NULL,
    destination TEXT NOT NULL,
    message     TEXT,
    ip_address  TEXT,
    user_agent  TEXT,
    referrer    TEXT,
    status      TEXT NOT NULL DEFAULT 'new',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_leads_created_at ON leads(created_at);
CREATE INDEX IF NOT EXISTS idx_leads_phone ON leads(phone);
CREATE INDEX IF NOT EXISTS idx_leads_email ON leads(email);
CREATE INDEX IF NOT EXISTS idx_leads_status ON leads(status);
CREATE INDEX IF NOT EXISTS idx_leads_destination ON leads(destination);

-- ============================================================
-- View: latest leads for admin dashboard (future)
-- ============================================================
CREATE VIEW IF NOT EXISTS v_leads_summary AS
SELECT
    id,
    name,
    city,
    phone,
    destination,
    status,
    created_at
FROM leads
ORDER BY created_at DESC;

-- ============================================================
-- Trigger: auto-update updated_at
-- ============================================================
CREATE TRIGGER IF NOT EXISTS trg_leads_updated_at
AFTER UPDATE ON leads
FOR EACH ROW
BEGIN
    UPDATE leads
    SET updated_at = CURRENT_TIMESTAMP
    WHERE id = OLD.id;
END;