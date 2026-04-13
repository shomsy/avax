CREATE TABLE auth_sessions (
    session_id TEXT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    created_at TIMESTAMPTZ NOT NULL,
    last_seen_at TIMESTAMPTZ NOT NULL,
    idle_expires_at TIMESTAMPTZ NOT NULL,
    absolute_expires_at TIMESTAMPTZ NOT NULL,
    ip_created TEXT NULL,
    user_agent_created TEXT NULL,
    revoked_at TIMESTAMPTZ NULL,
    revoke_reason TEXT NULL
);

CREATE INDEX auth_sessions_user_last_seen_idx ON auth_sessions (user_id, last_seen_at DESC);
CREATE INDEX auth_sessions_active_idx ON auth_sessions (user_id, revoked_at, idle_expires_at, absolute_expires_at);
