# Queue Status

Status: SCAFFOLD
Reason: Directory structure exists but no PHP implementation in PublicSurface. Queue broker and worker infrastructure exists at capability level but no public API surface.
Owns: Queue job dispatch and processing (planned).
Does not own: Scheduling, events, or database outbox.
Current behavior: MemoryQueue broker and RunWorkerLoop flow exist. No public Queue API.
Production-ready: No.
Tests: Central tests for MemoryQueue and RunWorkerLoop exist.
Health/doctor: Not applicable — scaffold public surface.
Roadmap: Public API planned for V5.9+.
Do not use until: Real public API exists.
