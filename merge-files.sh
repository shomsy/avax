#!/usr/bin/env bash
set -euo pipefail

node --input-type=module - "$@" <<'EOF'
import fs from 'node:fs';
import path from 'node:path';

const scriptName = 'merge-files.sh';
const args = process.argv.slice(2);

function usage() {
  console.error(`Usage: ${scriptName} <target-dir> [--exclude=png,jpg] [--include=md,yaml] [--dry-run]`);
}

function parseCsv(value) {
  if (!value) return new Set();
  return new Set(
    value
      .split(',')
      .map((part) => part.trim())
      .filter(Boolean),
  );
}

function compareStrings(left, right) {
  if (left < right) return -1;
  if (left > right) return 1;
  return 0;
}

function extensionKey(filePath) {
  const base = path.basename(filePath);
  if (base.includes('.')) {
    return path.extname(base).slice(1);
  }
  return base;
}

function readPackageName(rootDir) {
  const packageJsonPath = path.join(rootDir, 'package.json');
  if (!fs.existsSync(packageJsonPath)) {
    return '';
  }

  try {
    const raw = fs.readFileSync(packageJsonPath, 'utf8');
    const parsed = JSON.parse(raw);
    return typeof parsed.name === 'string' ? parsed.name.trim() : '';
  } catch {
    return '';
  }
}

if (args.length < 1) {
  usage();
  process.exit(1);
}

const targetArg = args[0];
const optionArgs = args.slice(1);
const includeExts = new Set();
const excludeExts = new Set();
let dryRun = false;

for (const arg of optionArgs) {
  if (arg.startsWith('--include=')) {
    for (const ext of parseCsv(arg.slice('--include='.length))) {
      includeExts.add(ext);
    }
    continue;
  }

  if (arg.startsWith('--exclude=')) {
    for (const ext of parseCsv(arg.slice('--exclude='.length))) {
      excludeExts.add(ext);
    }
    continue;
  }

  if (arg === '--dry-run') {
    dryRun = true;
    continue;
  }

  console.error(`Unknown option: ${arg}`);
  usage();
  process.exit(2);
}

if (includeExts.size > 0 && excludeExts.size > 0) {
  console.error('you cannot use --exclude and --include at the same time');
  process.exit(2);
}

const rootDir = path.resolve(targetArg);
let rootStat;

try {
  rootStat = fs.statSync(rootDir);
} catch {
  console.error(`directory '${targetArg}' does not exist`);
  process.exit(1);
}

if (!rootStat.isDirectory()) {
  console.error(`directory '${targetArg}' does not exist`);
  process.exit(1);
}

const targetBasename = path.basename(rootDir);
const outputFile = path.join(rootDir, `${targetBasename}.txt`);
// The merged .txt dump is a portable repo snapshot and can serve as a working backup during refactors.
const packageName = readPackageName(rootDir);

const ignoredDirs = new Set([
  '.cache',
  '.git',
  '.idea',
  '__pycache__',
  'artifacts',
  'build',
  'coverage',
  'dist',
  'node_modules',
  'storage',
  'tmp',
  'vendor',
]);

console.log(`Scanning directory: ${rootDir}`);
console.log(`Output file: ${outputFile}`);
console.log('Ignoring directories:');

for (const dir of [...ignoredDirs].sort(compareStrings)) {
  console.log(`- ${dir}`);
}

console.log('----------------------------------------');

const files = [];

function walk(currentDir) {
  const entries = fs
    .readdirSync(currentDir, { withFileTypes: true })
    .sort((a, b) => compareStrings(a.name, b.name));

  for (const entry of entries) {
    const currentPath = path.join(currentDir, entry.name);
    if (entry.isDirectory()) {
      if (ignoredDirs.has(entry.name) && currentPath !== rootDir) {
        continue;
      }
      walk(currentPath);
      continue;
    }

    files.push(currentPath);
  }
}

walk(rootDir);
files.sort(compareStrings);

let merged = 0;
let skipped = 0;
const chunks = [];

for (const absPath of files) {
  const rel = path.relative(rootDir, absPath).split(path.sep).join('/');

  if (absPath === outputFile) {
    console.log(`Skipping (generated output file): ${rel}`);
    skipped += 1;
    continue;
  }

  if (rel === 'agents.md') {
    console.log(`Skipping (ignored mirror file): ${rel}`);
    skipped += 1;
    continue;
  }

  const ext = extensionKey(absPath);
  if (includeExts.size > 0 && !includeExts.has(ext)) {
    console.log(`Skipping (not in include list): ${rel}`);
    skipped += 1;
    continue;
  }
  if (includeExts.size === 0 && excludeExts.has(ext)) {
    console.log(`Skipping (excluded by default or option): ${rel}`);
    skipped += 1;
    continue;
  }

  const data = fs.readFileSync(absPath);
  if (data.length > 0 && data.includes(0)) {
    console.log(`Skipping (binary or non-text): ${rel}`);
    skipped += 1;
    continue;
  }

  merged += 1;
  if (dryRun) {
    console.log(`[DRY-RUN] Would merge: ${rel}`);
    continue;
  }

  console.log(`Merging: ${rel}`);
  const text = data.toString('utf8');
  const normalizedText = text.replace(/[^\S\r\n]+$/gm, '');
  chunks.push(`=== ${rel} ===\n`);
  chunks.push(normalizedText);
  if (!normalizedText.endsWith('\n')) {
    chunks.push('\n');
  }
  chunks.push('\n');
}

if (!dryRun) {
  fs.writeFileSync(outputFile, chunks.join(''), 'utf8');

  if (packageName && packageName !== targetBasename) {
    const renamedOutput = path.join(rootDir, `${packageName}.txt`);
    fs.renameSync(outputFile, renamedOutput);
  }
}

const finalOutputFile = packageName && packageName !== targetBasename
  ? path.join(rootDir, `${packageName}.txt`)
  : outputFile;

console.log('----------------------------------------');
console.log('Done!');
console.log(`Merged files : ${merged}`);
console.log(`Skipped files: ${skipped}`);
console.log(`Output file  : ${finalOutputFile}`);
EOF
