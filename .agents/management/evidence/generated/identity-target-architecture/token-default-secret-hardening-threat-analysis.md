# Token Default Secret Hardening Threat Analysis

Date: 2026-05-21

## Asset

Identity token signing integrity and token bearer confidentiality assumptions.

## Threat

If the root Identity DSL signs tokens with a hardcoded default secret, any deployment using
the default DSL can mint or accept predictable tokens. A known static HMAC secret is a
security boundary bypass.

## Inputs

- `TOKEN_SECRET` from deployment configuration.
- `IdentityConfiguration::$tokenSecret` when explicitly provided by a composition root or test.

## Fail-Closed Behavior

`IdentityConfiguration::requireTokenSecret()` throws when no non-empty token secret is
configured. Root Identity runtime assembly refuses to construct token capabilities rather
than issuing tokens with a fallback secret.

## Sensitive Data Handling

The token secret is marked with `#[SensitiveParameter]` in `IdentityConfiguration`.
Evidence and tests use only non-production test strings.

## Residual Risk

The existing static `Identity` DSL still asks default configuration to read `TOKEN_SECRET`
when assembling the root runtime. That keeps the secret read in the configuration boundary,
but the static DSL lacks a richer application configuration bridge. A later root-runtime
composition slice should replace the static default path with configured runtime injection
or explicit bootstrap.
