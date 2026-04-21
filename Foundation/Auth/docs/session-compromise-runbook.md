# Session Compromise Runbook

Use this runbook when a browser session or tracked session family is suspected.

## Trigger Signals

- user reports an unknown active session
- privileged action appears from an unknown browser or location
- password reset or factor reset follows suspicious activity
- audit exporter flags repeated session failures or replay-like behavior

## Immediate Actions

1. identify the affected user and session ids
2. revoke the known session if the blast radius is clear
3. revoke all tracked sessions for the user if the blast radius is unclear
4. revoke refresh-token families for the same subject
5. require fresh login and MFA before continuing

## Follow-Up

- review login, logout, password change, factor reset, and admin elevation audit
  events
- preserve audit evidence under legal hold when required
- notify security operations if privileged posture was involved
- review browser origin, CSRF, and cookie deployment posture if the incident
  used browser-attached credentials

## Recovery Exit Criteria

- all suspicious sessions are revoked
- password and factor posture is re-established
- follow-up login succeeds with current policies
- audit evidence is exported and retained according to policy
