# Runtime Composition Facade Fixture Proof

**Date:** 2026-05-15

## 1. Fixtures Added

`tests/Composition/RuntimeComposition/RuntimeCompositionFacadeFixtureTest.php` — 8 tests, 16 assertions.

## 2. Fixture Results

| Fixture                                  | Expected                  | Actual                                                 | PASS? | Notes                                     |
|------------------------------------------|---------------------------|--------------------------------------------------------|------:|-------------------------------------------|
| Bad facade with `??= new RuntimeService` | Gate rejects pattern      | Gate source contains `/\?\?=\s*new\s+[A-Z]/` rejection |   YES | Proves lazy singleton rejected            |
| Bad facade with `?? new RuntimeService`  | Gate rejects pattern      | Gate source contains `/\?\?\s*new\s+[A-Z]/` rejection  |   YES | Proves null-coalescing fallback rejected  |
| Bad facade with `new VersionRegistry`    | Gate rejects pattern      | Gate medium severity catches `new *Registry`           |   YES | Proves registry instantiation caught      |
| Bad facade with `new HookRegistry`       | Gate rejects pattern      | Gate medium severity catches `new *Registry`           |   YES | Proves hook registry instantiation caught |
| Good provider-wired facade               | Gate PASS, 0 findings     | Gate PASS, ApiVersion/Pipeline 0 findings              |   YES | Proves correct facades pass               |
| Zero-scan is not PASS                    | Gate scans files          | Gate scans 3126+ files                                 |   YES | Proves gate does real work                |
| NOT_FOUND is not PASS                    | Gate file exists and runs | Gate exists, runs, exits 0/1                           |   YES | Proves gate is not missing                |
| Exit 0 with RED content is not PASS      | Exit code matches content | PASS → exit 0                                          |   YES | Proves gate exit code is truthful         |

## 3. Decision

Runtime composition gate is proven via explicit fixtures. Bad facade patterns are rejected. Good provider-wired facades
pass. No broad allowlists weaken the gate.
