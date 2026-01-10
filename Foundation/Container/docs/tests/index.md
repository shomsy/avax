# tests

## What This Folder Represents
This folder is a test chapter for the Container component. It exists to prove behavior, prevent regressions, and document expectations through executable examples.

### For Humans: What This Means (Represent)
If you want to trust the container, these tests are the receipts.

## What Belongs Here
- PHPUnit test cases that assert container behavior and invariants.
- Small fake/stub classes used only for test scenarios.
- Integration-style tests that wire multiple parts of the container together.

### For Humans: What This Means (Belongs)
This folder is for “prove it works”, not for “implement it”.

## What Does NOT Belong Here
- Production services and container logic.
- Long-lived fixtures that become a second configuration system.
- Tests that depend on network or external state without explicit isolation.

### For Humans: What This Means (Not Belongs)
If a test needs the internet or your machine’s state, it will betray you later.

## How Files Collaborate
Tests typically arrange a container/builder, act by resolving or running a flow, and assert results. Some tests share tiny helper classes inside the same file to keep scenarios readable.

### For Humans: What This Means (Collaboration)
You can read these tests like short stories: setup → action → expectation.
