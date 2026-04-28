# Password Rules

- `Argon2id` is preferred where available
- password change requires current proof and fresh-auth where policy demands it
- password reset revokes stale session and factor state
- password hashing and rehash decisions remain inside identity policy
