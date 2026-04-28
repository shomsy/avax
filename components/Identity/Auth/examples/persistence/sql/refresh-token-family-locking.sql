-- Rotate refresh tokens inside one transaction so reuse detection stays atomic.
BEGIN;

SELECT token_id, family_id, replacement_token_id, revoked_at
FROM auth_refresh_tokens
WHERE token_hash = :token_hash
    FOR UPDATE;

-- If replacement_token_id is already set here, treat the token as reused and
-- revoke the full family before commit.
UPDATE auth_refresh_token_families
SET revoked_at    = NOW(),
    revoke_reason = 'reuse_detected'
WHERE family_id = :family_id
  AND :reuse_detected = TRUE;

INSERT INTO auth_refresh_tokens (token_id,
                                 family_id,
                                 token_hash,
                                 user_id,
                                 client_id,
                                 expires_at)
VALUES (:replacement_token_id,
        :family_id,
        :replacement_token_hash,
        :user_id,
        :client_id,
        :replacement_expires_at);

UPDATE auth_refresh_tokens
SET replacement_token_id = :replacement_token_id,
    used_at              = NOW()
WHERE token_id = :current_token_id;

COMMIT;
