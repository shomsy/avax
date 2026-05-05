#!/usr/bin/env python3
from __future__ import annotations
import argparse
from pathlib import Path
TARGETS = {
  'database': {'new_root':'components/DataStack/Database/System','test_root':'tests/Unit/Components/DataStack/Database','hints': {'QueryBuilder':'PublicSurface','Database':'PublicSurface','Schema':'Capabilities/SchemaBuilding','Blueprint':'Capabilities/SchemaBuilding','Migration':'Capabilities/Migrations','Grammar':'Capabilities/Grammar','Connection':'Capabilities/Connections','Transaction':'Capabilities/Transactions','Timeline':'Capabilities/Observability','Query':'Capabilities/QueryBuilding'}},
  'persistence': {'new_root':'components/DataStack/Persistence/System','test_root':'tests/Unit/Components/DataStack/Persistence','hints': {'Persistence':'PublicSurface','Repository':'PublicSurface','Hydrat':'Capabilities/Hydration','UnitOfWork':'Capabilities/UnitOfWork','IdentityMap':'Capabilities/IdentityMap','Mapping':'Capabilities/Mapping','Entity':'Capabilities/Mapping','Change':'Capabilities/ChangeTracking'}}
}
def guess(component: str, rel: Path) -> str:
    cfg = TARGETS[component]; name = rel.name
    if 'test' in str(rel).lower(): return f'{cfg["test_root"]}/{name}'
    for needle, target in cfg['hints'].items():
        if needle.lower() in name.lower(): return f'{cfg["new_root"]}/{target}/{name}'
    return f'{cfg["new_root"]}/Capabilities/NeedsHumanDecision/{name}'
def main() -> int:
    p = argparse.ArgumentParser(); p.add_argument('--component', choices=TARGETS.keys(), required=True); p.add_argument('--staging', required=True); p.add_argument('--out', required=True); a = p.parse_args()
    staging = Path(a.staging); out = Path(a.out); out.parent.mkdir(parents=True, exist_ok=True)
    if not staging.exists(): raise SystemExit(f'Staging not found: {staging}')
    files = sorted(x for x in staging.rglob('*') if x.is_file())
    rows = [f'# Old To New Map — {a.component}', '', f'Staging: `{staging}`', '', '| Old/staged file | Suggested new target | Action | Status |','|---|---|---|---|']
    for file in files:
        rel = file.relative_to(staging); action = 'restore/slice' if file.suffix == '.php' else 'review'
        rows.append(f'| `{rel}` | `{guess(a.component, rel)}` | {action} | pending |')
    out.write_text('\n'.join(rows)+'\n', encoding='utf-8')
    print(f'Wrote {out}; mapped {len(files)} files')
    return 0
if __name__ == '__main__': raise SystemExit(main())
