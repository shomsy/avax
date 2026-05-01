# Success Measure Standard — Token Efficiency & Brief Quality

Version: 1.0.0
Status: Normative
Scope: `.agents/sessions/**`, `.agents/management/metrics/**`

To ensure the Agent OS is delivering on its promise of token efficiency and high context discipline, we track the following core metrics.

---

## 1) Core Metrics

### 1.1 Codex Avoidance Rate (CAR)
- **Definition**: The percentage of tasks resolved exclusively by "Mini" models (Mapper/Docs/CI) without invoking the Codex Executor.
- **Target**: > 30% for all "Investigation" and "Documentation" tasks.

### 1.2 Context Input Reduction (CIR)
- **Definition**: The average tokens passed to the Codex Executor for the final implementation, compared to the full repository size.
- **Target**: > 80% reduction (only 2-5 files identified).

### 1.3 Brief Accuracy Score (BAS)
- **Definition**: How often the Codex Executor found the Mapper's `relevant_files` to be sufficient to solve the task.
- **Target**: > 90% (Codex should rarely need to "find more files" itself).

### 1.4 Hallucination Rate (HR)
- **Definition**: Percentage of sub-agent artifacts that contain non-existent files or API endpoints (checked via validation).
- **Target**: < 1%.

---

## 2) Tracking Mechanism

Track these metrics in a session-specific `metrics.json` after each task completion.

```json
{
  "task_id": "session-123",
  "codex_invoked": true,
  "original_repo_tokens": 120000,
  "codex_input_tokens": 8000,
  "brief_accuracy": 1.0,
  "hallucination_detected": false
}
```

## 3) Relationship to Other Standards

| Standard | Relationship |
|:---|:---|
| `codex-usage-policy.md` | Metrics validate if Codex is being used according to the policy. |
| `artifact-standard.md` | Brief quality and accuracy directly impact metrics. |
