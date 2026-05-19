# False Positive Candidates: TODO-031

## FP-001: Individual HTD Evidence Files Do Not Exist
- **Affected IDs**: All 19 HTD IDs claiming oversized PublicSurface files (HTD-0423, HTD-0434, HTD-0460, HTD-0467, HTD-0476, HTD-0481, HTD-0483, HTD-0490, HTD-0498, HTD-0499, HTD-0500, HTD-0550, HTD-0637, HTD-0638, HTD-0639, HTD-0658, HTD-0659, HTD-0660, HTD-0661)
- **Reason**: No individual HTD-XXXX.md or similar evidence files exist in dual-review/strict-code-review/ or .agents/review/
- **Code evidence**: While oversized PublicSurface files DO exist (see confirmed findings), the specific HTD-to-file mapping from source-finding-coverage.md cannot be individually verified
- **Classification**: FALSE_POSITIVE_CANDIDATE for individual claim mapping; PARTIAL for overall category

## FP-002: Individual SCR Evidence Files Do Not Exist
- **Affected IDs**: All SCR IDs in TODO-031 (SCR-0056, SCR-0060, SCR-0103, SCR-0105, SCR-0107, SCR-0108, SCR-0109, SCR-0110, SCR-0114, SCR-0116, SCR-0262, SCR-0359, SCR-0361-0367, SCR-0392, SCR-0394, SCR-0423, SCR-0434, SCR-0460, SCR-0467, SCR-0476, SCR-0481, SCR-0483, SCR-0490, SCR-0498, SCR-0499, SCR-0500, SCR-0550, SCR-0637, SCR-0638, SCR-0639, SCR-0658, SCR-0659, SCR-0660, SCR-0661, SCR-0662, SCR-0663)
- **Reason**: No individual SCR-XXXX.md evidence files found in dual-review/strict-code-review/ or .agents/review/
- **Classification**: FALSE_POSITIVE_CANDIDATE for individual claim verification

## FP-003: DR Supplemental IDs Without Evidence Files
- **Affected IDs**: DR-0057, DR-0061, DR-0104, DR-0106, DR-0108, DR-0109, DR-0110, DR-0111, DR-0115, DR-0117, DR-0263, DR-0360, DR-0362, DR-0363, DR-0364, DR-0365, DR-0366, DR-0367, DR-0368, DR-0424, DR-0435, DR-0461, DR-0468, DR-0477, DR-0482, DR-0484, DR-0491, DR-0499, DR-0500, DR-0501, DR-0551, DR-0638, DR-0639, DR-0640, DR-0659, DR-0660, DR-0661, DR-0662, DR-0663, DR-0664
- **Reason**: These DR IDs are recorded in the source-finding-coverage.md table but no individual DR evidence files exist
- **Assessment**: These appear to be from a discipline review pass that generated aggregate counts but not individual finding files. The aggregate discipline review counts are real (source-finding-coverage.md has 305 NEEDS_VERIFICATION entries), but individual claim details cannot be verified
- **Classification**: FALSE_POSITIVE_CANDIDATE for individual claim verification

## FP-004: Null-Coalescing Claims in Approved Configuration Zones
- **Affected IDs**: HTD IDs mapping to `AssembleAuthIdentityGraph.php` and `BootDslBuilder.php`
- **Reason**: These files are in Configuration/Assembly zones where `?? new` fallback is acceptable per DI governance ("Configuration assembles", "fallback construction outside approved Configuration/Assembly zones" is the forbidden pattern)
- **Code evidence**: `AssembleAuthIdentityGraph.php` is under `System/Configuration/Assembly/`; `BootDslBuilder.php` is under `System/Configuration/BootDsl/`
- **Classification**: FALSE_POSITIVE_CANDIDATE for these specific instances; the pattern itself is legitimate in Configuration zones

## FP-005: HTD-to-File Mapping Count Mismatch
- **Claim**: 19 HTD IDs for oversized PublicSurface
- **Reality**: 16 actual files exceed 150 lines; some HTD IDs may reference files that no longer exist, were refactored, or were miscounted
- **Classification**: PARTIAL - at least 3 HTD IDs reference files or line counts that do not match current codebase state
