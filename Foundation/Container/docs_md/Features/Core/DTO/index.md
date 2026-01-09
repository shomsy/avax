# Features/Core/DTO

## What This Folder Represents
Small immutable data transfer objects used to communicate outcomes and diagnostics in a structured way. They exist to replace ad-hoc arrays and inconsistent return shapes.

### For Humans: What This Means
These are simple “result envelopes” the container passes around so you can inspect what happened.

## What Belongs Here
DTOs like `ErrorDTO`, `SuccessDTO`, and `InjectionReport`.

### For Humans: What This Means
If it’s a small readonly object used to report results, it belongs here.

## What Does NOT Belong Here
Business entities or complex domain models.

### For Humans: What This Means
These DTOs are about container operations, not your application’s business data.

## How Files Collaborate
Guards and validators often return `ErrorDTO`/`SuccessDTO`. Injection tooling can produce `InjectionReport`. Exceptions are thrown for hard failures; DTOs are used when you want structured, non-exception outcomes.

### For Humans: What This Means
DTOs are for “tell me what happened” without throwing; exceptions are for “stop, we can’t continue.”
