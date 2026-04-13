CREATE TABLE auth_refresh_token_families (
    family_id TEXT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    client_id TEXT NULL,
    created_at TIMESTAMPTZ NOT NULL,
    revoked_at TIMESTAMPTZ NULL,
    revoke_reason TEXT NULL
);

CREATE TABLE auth_refresh_tokens (
    token_id TEXT PRIMARY KEY,
    family_id TEXT NOT NULL REFERENCES auth_refresh_token_families (family_id),
    token_hash TEXT NOT NULL UNIQUE,
    user_id BIGINT NOT NULL,
    client_id TEXT NULL,
    replacement_token_id TEXT NULL,
    expires_at TIMESTAMPTZ NOT NULL,
    used_at TIMESTAMPTZ NULL,
    revoked_at TIMESTAMPTZ NULL,
    reuse_detected_at TIMESTAMPTZ NULL
);

CREATE INDEX auth_refresh_tokens_family_idx ON auth_refresh_tokens (family_id);
CREATE INDEX auth_refresh_tokens_hash_idx ON auth_refresh_tokens (token_hash);
