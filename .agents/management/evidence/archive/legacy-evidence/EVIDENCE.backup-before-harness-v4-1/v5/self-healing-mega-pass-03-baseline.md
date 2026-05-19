# V5 Self-Healing Mega Pass 03 — Baseline

**Date:** 2026-05-10
**Branch:** main

## Current Validation Status

| Check                                | Result                               |
|--------------------------------------|--------------------------------------|
| PHPUnit                              | GREEN — 7475 tests, 21729 assertions |
| PHPStan                              | CLEAN — 0 errors                     |
| Composer validate                    | PASS                                 |
| Security blockers                    | PASS                                 |
| Security naming                      | GREEN                                |
| Component adoption gate              | PASS — 8 checks                      |
| Raw file gate: MIGRATE_TO_FILESYSTEM | 0                                    |
| Raw file gate: MIGRATE_TO_STORAGE    | 0                                    |
| Raw file gate: NEEDS_DESIGN_DECISION | 11                                   |

## 11 NEEDS_DESIGN_DECISION Items

1. FileBackedHmacKeyRingCodec.php:55 — file_get_contents (token key file)
2. DataExporter.php:46 — fclose (privacy export stream)
3. NativeYamlParser.php:37 — file_get_contents (YAML parsing)
4. Question.php:43 — fclose (CLI UI stdin)
5. Confirm.php:42 — fclose (CLI UI stdin)
6. FileLogWriter.php:66 — fwrite (batch log write)
7. FileLogWriter.php:98 — fclose (batch log write)
8. FileLogWriter.php:123 — fopen (batch log write)
9. UploadedFile.php:24 — fopen (HTTP upload)
10. CsvFormat.php:30 — fclose (CSV output stream)
11. CsvFormatter.php:30 — fclose (CSV output stream)

## Filesystem API Gaps

- No stream read/write (fopen/fwrite/fclose equivalent)
- No file type distinction (isFile vs isDirectory)
- No uploaded file handling
- No CSV/YAML structured file writing
- No atomic write with lock

## Gate Outputs

Raw file gate: 0 MIGRATE, 11 NEEDS_DESIGN_DECISION
Component adoption gate: PASS, 8 checks verified

## Next Action

Part 1: Resolve all 11 NEEDS_DESIGN_DECISION items
