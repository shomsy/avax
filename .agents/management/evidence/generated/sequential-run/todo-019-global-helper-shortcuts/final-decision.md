# Final Decision — TODO-019 Global Helper Shortcuts

- Status: TODO_CLOSED
- Decision: Duplicate `csrf_token()` removed from HTTP/System shortcuts.php (canonical owner is HTTP/Security). Deprecated `X-XSS-Protection` header replaced with `Content-Security-Policy` and `Strict-Transport-Security`. 5 new tests prove load-order correctness, canonical ownership, and header compliance.
- Evidence path: `.agents/management/evidence/generated/sequential-run/todo-019-global-helper-shortcuts/`
- Validation: Focused validation GREEN — security shortcut tests PASS, CSRF tests PASS, governance gates PASS
- Accepted YELLOW: global `app()` service locator calls (thin compatibility shims, documented as temporary); PublicSurface file length (17 thin one-liners)
