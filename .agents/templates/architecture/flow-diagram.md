# <Flow Name>

> Purpose: <one sentence describing what flow this diagram shows>

## Diagram

```mermaid
sequenceDiagram
    autonumber
    participant ParticipantA as <Display Name>
    participant ParticipantB as <Display Name>
    participant ParticipantC as <Display Name>

    ParticipantA->>ParticipantB: <action>(<data>)
    ParticipantB->>ParticipantC: <action>(<data>)
    ParticipantC-->>ParticipantB: <response>
    ParticipantB-->>ParticipantA: <result>
```

## Step-by-Step

1. **<Step 1>:** <what happens, who does it, why>
2. **<Step 2>:** <what happens, who does it, why>
3. **<Step 3>:** <what happens, who does it, why>

## Participants

| Participant | Role | Key Files |
|-------------|------|-----------|
| <Participant> | <what it does in this flow> | `<file-path>` |
| <Participant> | <what it does in this flow> | `<file-path>` |

## Failure Modes

| Step | Failure | Behavior |
|------|---------|----------|
| <Step> | <what can go wrong> | <how the system responds> |
| <Step> | <what can go wrong> | <how the system responds> |

## Related

- ADRs:
- Dictionary:
- Tests:
