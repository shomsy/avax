# Session Context

This folder contains session-specific context for agent interactions.

## Structure

Each session should create a subfolder with the session ID:

```
sessions/
├── <SESSION_ID>/
│   ├── session_memory.md    # Session-specific context
│   ├── current-task.md      # Current task being worked on
│   └── transcript.json      # Session activity log
```

## Session File Templates

### session_memory.md

```markdown
# Session: [SESSION_ID]
Created: [YYYY-MM-DD HH:MM TZ]

## Current Task
- Task: [description]
- Status: [in_progress|completed|blocked]
- Started: [timestamp]

## Context
- Feature: [what we're working on]
- Files: [relevant files]

## Notes
- [key decisions, findings]
```

### current-task.md

```markdown
# Current Task: [TASK_ID]

## Description
[What needs to be done]

## Acceptance Criteria
- [ ] Criteria 1
- [ ] Criteria 2

## Progress
[Current status and notes]
```

## Session Rules

- Create new session folder for each significant interaction
- Track current task in `current-task.md`
- Log significant decisions in `session_memory.md`
- Clean up old sessions periodically

## Session ID Format

Use: `YYYYMMDD-HHMMSS-随机字符串`

Example: `20260406-143022-a1b2c3`