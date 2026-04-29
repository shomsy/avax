# Session Component 1:1 Implementation Plan
Current Status: components/HTTP/Session/ is EMPTY. Target: 60+ files per master-plan.md/avax.txt.

## Phase 1: Core Skeleton (10 files) ✅
- [x] Create System/PublicSurface/ (3 files: Session.php, SessionInterface.php, SessionScope.php)
- [x] Create System/Configuration/ (2/4 files: BuildSession.php, SessionConfig.php)  
- [ ] Create System/Configuration/SessionCookieConfig.php
- [ ] Create System/Configuration/SessionProvider.php
- [ ] Create System/Foundation/Exceptions/ (0/3 files)

## Phase 2: StartSession Flow (8 files)
- [ ] System/Flows/StartSession/StartSession.php
- [ ] System/Flows/StartSession/CreateSessionId.php
- [ ] System/Flows/StartSession/LoadSessionState.php
- [ ] etc. (full from avax.txt)

## Phase 3: ManageSessionValue Flow (12 files)
- [ ] System/Flows/ManageSessionValue/GetSessionValue.php
- [ ] System/Flows/ManageSessionValue/PutSessionValue.php

## Phase 4: Capabilities (20 files)
- [ ] System/Capabilities/SessionDriverInterface.php
- [ ] System/Capabilities/SessionStore/
- [ ] System/Capabilities/SessionCookie/

## Phase 5: Supporting Flows (15 files)
- [ ] EndSession/
- [ ] RegenerateSessionId/
- [ ] TakeSessionSnapshot/

## Phase 6: Events & Audit (5 files)
- [ ] System/SessionEvents/
- [ ] System/SessionAudit/

## Phase 7: Quality Gates
- [ ] Namespace normalization
- [ ] Tests moved/added
- [ ] Architecture checkers
- [ ] Full PHPUnit
- [ ] Docs mirror

## Phase 8: Integration & Cleanup
- [ ] Framework registration
- [ ] Delete empty legacy Session/
- [ ] Final report in Code-Review-And-ToDo/
