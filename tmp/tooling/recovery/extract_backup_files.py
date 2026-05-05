#!/usr/bin/env python3
from __future__ import annotations
import argparse, re
from pathlib import Path
HEADER_RE = re.compile(r"^===\s+(.+?)\s+===$")

def parse_backup(path: Path) -> dict[str, str]:
    result: dict[str, list[str]] = {}
    current: str | None = None
    for line in path.read_text(encoding='utf-8', errors='replace').splitlines(keepends=True):
        m = HEADER_RE.match(line.strip())
        if m:
            current = m.group(1).strip()
            result[current] = []
            continue
        if current is not None:
            result[current].append(line)
    return {k: ''.join(v) for k, v in result.items()}

def safe(base: Path, old: str) -> Path:
    clean = old.strip().lstrip('/')
    if '..' in Path(clean).parts:
        raise RuntimeError(f'Unsafe path: {old}')
    return base / clean

def main() -> int:
    p = argparse.ArgumentParser()
    p.add_argument('--backup', default='avax-backup.txt')
    p.add_argument('--prefix', action='append', required=True)
    p.add_argument('--out', required=True)
    p.add_argument('--report', required=True)
    a = p.parse_args()
    backup = Path(a.backup)
    if not backup.exists(): raise SystemExit(f'Backup file not found: {backup}')
    out = Path(a.out); report = Path(a.report)
    out.mkdir(parents=True, exist_ok=True); report.parent.mkdir(parents=True, exist_ok=True)
    selected = {k:v for k,v in parse_backup(backup).items() if any(k.startswith(pref) for pref in a.prefix)}
    rows = ['# Extracted Backup Files','',f'Backup: `{backup}`','', '| Old path | Output path | Bytes |','|---|---|---:|']
    for old, content in sorted(selected.items()):
        target = safe(out, old); target.parent.mkdir(parents=True, exist_ok=True); target.write_text(content, encoding='utf-8')
        rows.append(f'| `{old}` | `{target}` | {len(content.encode("utf-8"))} |')
    report.write_text('\n'.join(rows)+'\n', encoding='utf-8')
    print(f'Extracted {len(selected)} files')
    print(f'Report: {report}')
    return 0
if __name__ == '__main__': raise SystemExit(main())
