# Learning Config

This file documents the default operating thresholds for the reusable learning
runtime.

| Key | Default | Meaning |
|:---|:---:|:---|
| `observe_max_lines` | `50000` | Maximum lines before observation archive rotation |
| `observe_archive_batch` | `25000` | Number of oldest lines moved during rotation |
| `detect_min_occurrences` | `3` | Minimum repeated pattern count before instinct creation |
| `detect_max_evidence` | `5` | Evidence samples written into each instinct file |
| `detect_cooldown_seconds` | `60` | Minimum time between automated analysis passes |

Projects MAY override these thresholds locally if their scale or tooling needs
justify it.
