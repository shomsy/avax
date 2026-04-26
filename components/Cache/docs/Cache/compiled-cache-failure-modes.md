---
title: compiled-cache-failure-modes
owner: CompiledCache Team
last_reviewed: 2026-04-25
classification: internal
---

# Compiled Cache Failure Modes

## Missing Artifact

**Cause**: Artifact file does not exist.

**Symptom**: `CompiledCacheWasMissing` or auto-rebuild.

**Resolution**: Rebuild via builder function.

## Stale Artifact

**Cause**: Source file changed (mtime or checksum).

**Symptom**: Freshness check returns `STALE`.

**Resolution**: Rebuild artifact.

## Corrupted Artifact

**Cause**: PHP file syntax error or invalid return.

**Symptom**: `CompiledCacheCouldNotBeRead` with reason.

**Resolution**: Delete and rebuild, or fail with error.

## Unwritable Directory

**Cause**: Permission denied or disk full.

**Symptom**: `CompiledCacheCouldNotBeWritten`.

**Resolution**: Check permissions, disk space.

## Invalid Source Path

**Cause**: Source file does not exist.

**Symptom**: `InvalidArgumentException` in source validation.

**Resolution**: Fix source file path.

## Invalid Payload

**Cause**: Builder returned closure, resource, or unsupported type.

**Symptom**: `CompiledCachePayloadWasInvalid`.

**Resolution**: Return array/scalar only.

## Atomic Rename Failed

**Cause**: OS-level rename error.

**Symptom**: Temporary file remains, final missing.

**Resolution**: Clean temp files, retry.

## Manifest Write Failed

**Cause**: Disk full or permission denied.

**Symptom**: Artifact written but manifest not updated.

**Resolution**: Manual manifest repair.

## Path Traversal Attempt

**Cause**: Invalid artifact name with `..` or slash.

**Symptom**: `InvalidArgumentException`.

**Resolution**: Use valid artifact name.