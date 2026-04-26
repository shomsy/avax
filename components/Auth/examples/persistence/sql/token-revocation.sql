CREATE TABLE auth_token_revocations
(
    token_id   TEXT PRIMARY KEY,
    expires_at TIMESTAMPTZ NOT NULL,
    revoked_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX auth_token_revocations_expiry_idx ON auth_token_revocations (expires_at);
