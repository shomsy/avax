CREATE TABLE auth_audit_export_cursors
(
    exporter_name          TEXT PRIMARY KEY,
    last_exported_at       TIMESTAMPTZ NULL,
    last_event_name        TEXT NULL,
    last_event_fingerprint TEXT NULL,
    updated_at             TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
