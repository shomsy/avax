# Pass 1 Worktree Baseline

Date: 2026-05-15
Branch: main

## Git Status

```
M .agents/how-to/how-to.txt
?? EVIDENCE/hardening/
```

## Classification

| File                                                   | Category               | Will This Pass Touch?         |
|--------------------------------------------------------|------------------------|-------------------------------|
| .agents/how-to/how-to.txt                              | pre-existing user work | NO — unrelated governance doc |
| EVIDENCE/hardening/00-runtime-composition-preflight.md | this pass evidence     | YES — created by this pass    |

## Dirty Files

- Only `.agents/how-to/how-to.txt` is modified — pre-existing work, not touched by this pass.
- `EVIDENCE/hardening/` is new — evidence directory for this pass.

## Rules Applied

- No user/pre-existing work reverted.
- No unrelated dirty files mixed into commits.
- No hidden worktree changes.

## Files This Pass Will Edit

### Runtime composition leak fixes:

1. framework/System/PublicSurface/App.php — remove per-request `new` composition
2. framework/System/PublicSurface/Avax.php — boot-time composition, acceptable but verify
3. framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php — inject scope objects
4. framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php — pre-build RouteCollection
5. components/Operations/Events/System/Foundation/EventEmitter.php — inject resolver/invoker
6. components/Operations/Events/System/Foundation/GlobalEventListenerState.php — remove ??= new fallback
7. components/Operations/Concurrency/System/PublicSurface/Concurrency.php — remove ??= new Build->build()
8. components/Operations/Concurrency/System/Flows/RaceTasks/RaceTasks.php — inject runtime

### Gate strengthening:

9. tooling/refactor/check-runtime-composition-leaks.php — context-aware, no broad allowlists

### Evidence:

10. EVIDENCE/hardening/02-runtime-composition-leak-closure.md
11. EVIDENCE/hardening/10-gate-reality-proof.md
12. EVIDENCE/hardening/11-runtime-composition-recursive-review.md
13. EVIDENCE/hardening/12-final-validation.md
