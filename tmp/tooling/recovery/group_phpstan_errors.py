#!/usr/bin/env python3
from __future__ import annotations
import argparse
from collections import Counter, defaultdict
from pathlib import Path
FAMILIES = {
 'missing_iterable_value_type':['no value type specified in iterable','has no value type specified'],
 'missing_generic':['generic','template type'],
 'unknown_class':['unknown class','class not found','not found'],
 'unknown_method':['call to an undefined method','undefined method'],
 'unknown_property':['access to an undefined property','undefined property'],
 'wrong_argument':['parameter #','expects','given'],
 'wrong_return':['should return','return type'],
 'mixed':['mixed'],
 'array_shape':['array shape','offset'],
 'phpdoc':['phpdoc','@param','@return','docblock'],
}
def classify(line: str) -> str:
    low = line.lower()
    for fam, needles in FAMILIES.items():
        if any(n in low for n in needles): return fam
    return 'other'
def main() -> int:
    p = argparse.ArgumentParser(); p.add_argument('--input', required=True); p.add_argument('--out', required=True); a = p.parse_args()
    inp = Path(a.input); out = Path(a.out); out.parent.mkdir(parents=True, exist_ok=True)
    if not inp.exists(): raise SystemExit(f'Input not found: {inp}')
    lines = [x.strip() for x in inp.read_text(encoding='utf-8', errors='replace').splitlines() if x.strip()]
    counts = Counter(); examples = defaultdict(list)
    for line in lines:
        fam = classify(line); counts[fam] += 1
        if len(examples[fam]) < 10: examples[fam].append(line)
    rows = ['# PHPStan Error Families','',f'Input: `{inp}`','', '| Family | Count |','|---|---:|']
    for fam, count in counts.most_common(): rows.append(f'| {fam} | {count} |')
    rows += ['', '## Examples', '']
    for fam in sorted(examples): rows += [f'### {fam}', ''] + [f'- `{x}`' for x in examples[fam]] + ['']
    out.write_text('\n'.join(rows), encoding='utf-8')
    print(f'Wrote {out}')
    return 0
if __name__ == '__main__': raise SystemExit(main())
