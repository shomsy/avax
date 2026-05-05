#!/usr/bin/env python3
from __future__ import annotations
import subprocess
REPORT_EXTENSIONS = ('.md','.txt','.json','.xml','.neon','.cache')
def changed_files() -> list[str]:
    out = subprocess.check_output(['git','status','--short'], text=True)
    files = []
    for line in out.splitlines():
        if not line.strip(): continue
        path = line[3:].strip() if len(line) > 3 else line.strip()
        if ' -> ' in path: path = path.split(' -> ',1)[1].strip()
        files.append(path)
    return files
def is_report_only(path: str) -> bool:
    return path.startswith('Code-Review-And-ToDo/') or path.startswith('.phpunit') or path.startswith('tooling/recovery/') or path == 'Makefile.recovery' or path.endswith(REPORT_EXTENSIONS)
def main() -> int:
    files = changed_files()
    if not files: print('No changed files.'); return 1
    print('Changed files:')
    for f in files: print(f'- {f}')
    production = [f for f in files if not is_report_only(f)]
    if not production:
        print('\nERROR: Report/tooling-only change. Do not mark RESTORED/COMPLETE.')
        print('Allowed status: AUDIT-ONLY or VERIFIED-PRESENT.')
        return 2
    print('\nProduction-impacting files detected:')
    for f in production: print(f'- {f}')
    return 0
if __name__ == '__main__': raise SystemExit(main())
