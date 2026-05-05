#!/usr/bin/env python3
from __future__ import annotations
import argparse
from pathlib import Path
LANES = ['System/PublicSurface','System/Flows','System/Capabilities','System/Configuration','System/Foundation/Values','System/Foundation/Failure']
def main() -> int:
    p = argparse.ArgumentParser(); p.add_argument('--component', required=True); p.add_argument('--purpose', required=True); a = p.parse_args()
    root = Path('components') / a.component
    for lane in LANES: (root / lane).mkdir(parents=True, exist_ok=True)
    doc = root / 'how-this-works.md'
    if not doc.exists():
        doc.write_text('# '+a.component+'\n\n## Purpose\n\n'+a.purpose+'\n\n## Recovery note\n\nThis structure was stamped for muscle recovery. Structure is not behavior.\nDo not mark this component restored until production behavior, tests, and PHPStan proof exist.\n', encoding='utf-8')
    print(f'Stamped structure: {root}')
    return 0
if __name__ == '__main__': raise SystemExit(main())
