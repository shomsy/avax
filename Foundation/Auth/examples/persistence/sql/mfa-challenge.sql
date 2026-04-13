CREATE TABLE auth_mfa_challenges (
    challenge_id TEXT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    session_candidate_id TEXT NULL,
    challenge_type TEXT NOT NULL,
    expires_at TIMESTAMPTZ NOT NULL,
    verified_at TIMESTAMPTZ NULL,
    attempt_count INT NOT NULL DEFAULT 0,
    blocked_until TIMESTAMPTZ NULL
);

CREATE INDEX auth_mfa_challenges_user_idx ON auth_mfa_challenges (user_id, expires_at);
