#!/usr/bin/env bash
set -euo pipefail

node --input-type=module - "$@" <<'NODE'
import fs from 'node:fs';
import path from 'node:path';
import { execSync } from 'node:child_process';

const SCRIPT_NAME = 'merge-files.sh';
const DEFAULT_PIECES = 1;
const MINIMUM_PIECES = 1;
const MAXIMUM_PIECES = 1000;

const DEFAULT_IGNORED_DIRECTORY_NAMES = new Set([
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

const IGNORED_MIRROR_FILES = new Set([
  'agents.md',
]);

function usage() {
  console.error(
    `Usage: ${SCRIPT_NAME} <target-dir> [--except=.qoder,EVIDENCE/archive] [--exclude=png,jpg] [--include=md,yaml] [--pieces=4] [--dry-run]`,
  );
}

function fail(message, exitCode = 1) {
  console.error(message);
  process.exit(exitCode);
}

function compareStrings(left, right) {
  if (left < right) return -1;
  if (left > right) return 1;

  return 0;
}

function parseCsv(value) {
  if (!value) return [];

  return value
    .split(',')
    .map((part) => part.trim())
    .filter(Boolean);
}

function parsePositiveInteger(value, optionName) {
  if (!/^\d+$/.test(value)) {
    fail(`${optionName} must be a positive integer`, 2);
  }

  const parsed = Number.parseInt(value, 10);

  if (!Number.isSafeInteger(parsed) || parsed < MINIMUM_PIECES || parsed > MAXIMUM_PIECES) {
    fail(`${optionName} must be between ${MINIMUM_PIECES} and ${MAXIMUM_PIECES}`, 2);
  }

  return parsed;
}

function normalizeDirectoryRule(rule) {
  return rule
    .trim()
    .replaceAll('\\', '/')
    .replace(/^\.\/+/, '')
    .replace(/\/+/g, '/')
    .replace(/\/+$/g, '');
}

function normalizeExtension(extension) {
  return extension
    .trim()
    .replace(/^\.+/, '')
    .toLowerCase();
}

function relativePath(rootDir, absPath) {
  return path.relative(rootDir, absPath).split(path.sep).join('/');
}

function extensionKey(filePath) {
  const base = path.basename(filePath);

  if (base.includes('.')) {
    return path.extname(base).slice(1).toLowerCase();
  }

  return base.toLowerCase();
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

function fileStemFromPackageName(packageName) {
  return packageName
    .trim()
    .replace(/[<>:"/\\|?*\x00-\x1F]/g, '_')
    .replace(/\s+/g, '-')
    .replace(/_+/g, '_')
    .replace(/^\.+/, '')
    .replace(/[._-]+$/g, '');
}

function outputFileFor(rootDir, targetBasename, packageName) {
  const packageStem = fileStemFromPackageName(packageName);
  const outputStem = packageStem !== '' ? packageStem : targetBasename;

  return path.join(rootDir, `${outputStem}.txt`);
}

function outputPiecePath(outputFile, pieceNumber, totalPieces) {
  const extension = path.extname(outputFile) || '.txt';
  const base = outputFile.slice(0, -extension.length);

  return `${base}.part-${pieceNumber}-of-${totalPieces}${extension}`;
}

function outputFamilyFor(outputFile) {
  const extension = path.extname(outputFile) || '.txt';
  const directory = path.dirname(outputFile);
  const fileName = path.basename(outputFile);
  const stem = fileName.slice(0, -extension.length);

  return {
    directory,
    extension,
    stem,
    singleFile: outputFile,
  };
}

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function isGeneratedOutputFile(absPath, outputFamilies) {
  for (const family of outputFamilies) {
    if (absPath === family.singleFile) {
      return true;
    }

    if (path.dirname(absPath) !== family.directory) {
      continue;
    }

    const fileName = path.basename(absPath);
    const startsLikePiece = fileName.startsWith(`${family.stem}.part-`);
    const endsLikePiece = fileName.endsWith(family.extension);

    if (!startsLikePiece || !endsLikePiece) {
      continue;
    }

    const piecePattern = new RegExp(
      `^${escapeRegExp(family.stem)}\\.part-\\d+-of-\\d+${escapeRegExp(family.extension)}$`,
    );

    if (piecePattern.test(fileName)) {
      return true;
    }
  }

  return false;
}

function parseOptions(args) {
  if (args.length < 1) {
    usage();
    process.exit(1);
  }

  const options = {
    targetArg: args[0],
    includeExtensions: new Set(),
    excludeExtensions: new Set(),
    exceptRules: new Set(),
    dryRun: false,
    pieces: DEFAULT_PIECES,
    canonical: false,
    aggregate: false,
  };

  for (const arg of args.slice(1)) {
    if (arg.startsWith('--include=')) {
      for (const extension of parseCsv(arg.slice('--include='.length))) {
        const normalized = normalizeExtension(extension);

        if (normalized !== '') {
          options.includeExtensions.add(normalized);
        }
      }

      continue;
    }

    if (arg.startsWith('--exclude=')) {
      for (const extension of parseCsv(arg.slice('--exclude='.length))) {
        const normalized = normalizeExtension(extension);

        if (normalized !== '') {
          options.excludeExtensions.add(normalized);
        }
      }

      continue;
    }

    if (arg.startsWith('--except=')) {
      for (const rule of parseCsv(arg.slice('--except='.length))) {
        const normalized = normalizeDirectoryRule(rule);

        if (normalized !== '') {
          options.exceptRules.add(normalized);
        }
      }

      continue;
    }

    if (arg.startsWith('--pieces=')) {
      options.pieces = parsePositiveInteger(arg.slice('--pieces='.length), '--pieces');
      continue;
    }

    if (arg === '--dry-run') {
      options.dryRun = true;
      continue;
    }

    if (arg === '--canonical') {
      options.canonical = true;
      continue;
    }

    if (arg === '--aggregate') {
      options.aggregate = true;
      continue;
    }

    console.error(`Unknown option: ${arg}`);
    usage();
    process.exit(2);
  }

  if (options.includeExtensions.size > 0 && options.excludeExtensions.size > 0) {
    fail('you cannot use --exclude and --include at the same time', 2);
  }

  return options;
}

function resolveRootDirectory(targetArg) {
  const rootDir = path.resolve(targetArg);

  let rootStat;

  try {
    rootStat = fs.statSync(rootDir);
  } catch {
    fail(`directory '${targetArg}' does not exist`);
  }

  if (!rootStat.isDirectory()) {
    fail(`directory '${targetArg}' does not exist`);
  }

  return rootDir;
}

function shouldIgnoreDirectory(dirName, relDir, exceptRules, canonical) {
  if (DEFAULT_IGNORED_DIRECTORY_NAMES.has(dirName)) {
    return true;
  }

  if (canonical) {
    if (relDir.startsWith('.agents/archive') || 
        relDir.startsWith('EVIDENCE/archive') || 
        relDir.startsWith('.agents/management/evidence/archive') ||
        relDir.startsWith('projects') ||
        relDir.startsWith('artifacts')) {
      return true;
    }
  }

  for (const rule of exceptRules) {
    const isPathRule = rule.includes('/');

    if (!isPathRule && dirName === rule) {
      return true;
    }

    if (isPathRule && (relDir === rule || relDir.startsWith(`${rule}/`))) {
      return true;
    }
  }

  return false;
}

function collectTextFiles(rootDir, options, outputFamilies) {
  const exceptRules = options.exceptRules;
  const canonical = options.canonical;
  const files = [];

  function walk(currentDir) {
    const entries = fs
      .readdirSync(currentDir, { withFileTypes: true })
      .sort((a, b) => compareStrings(a.name, b.name));

    for (const entry of entries) {
      const currentPath = path.join(currentDir, entry.name);

      if (entry.isSymbolicLink()) {
        continue;
      }

      if (entry.isDirectory()) {
        const relDir = relativePath(rootDir, currentPath);

        if (shouldIgnoreDirectory(entry.name, relDir, exceptRules, canonical)) {
          continue;
        }

        walk(currentPath);
        continue;
      }

      if (entry.isFile() && !isGeneratedOutputFile(currentPath, outputFamilies)) {
        files.push(currentPath);
      }
    }
  }

  walk(rootDir);

  return files.sort(compareStrings);
}

function shouldSkipByExtension(filePath, includeExtensions, excludeExtensions) {
  const extension = extensionKey(filePath);

  if (includeExtensions.size > 0 && !includeExtensions.has(extension)) {
    return 'not in include list';
  }

  if (includeExtensions.size === 0 && excludeExtensions.has(extension)) {
    return 'excluded by extension';
  }

  return '';
}

function isBinaryBuffer(data) {
  return data.length > 0 && data.includes(0);
}

function normalizeText(text) {
  return text.replace(/[^\S\r\n]+$/gm, '');
}

function buildFileBlock(relPath, text) {
  const normalizedText = normalizeText(text);
  const lines = [`=== ${relPath} ===\n`, normalizedText];

  if (!normalizedText.endsWith('\n')) {
    lines.push('\n');
  }

  lines.push('\n');

  return lines.join('');
}

function mergeFiles(rootDir, files, options) {
  const chunks = [];
  const stats = {
    merged: 0,
    skipped: 0,
  };

  for (const absPath of files) {
    const rel = relativePath(rootDir, absPath);

    if (IGNORED_MIRROR_FILES.has(rel)) {
      console.log(`Skipping (ignored mirror file): ${rel}`);
      stats.skipped += 1;
      continue;
    }

    const extensionReason = shouldSkipByExtension(
      absPath,
      options.includeExtensions,
      options.excludeExtensions,
    );

    if (extensionReason !== '') {
      console.log(`Skipping (${extensionReason}): ${rel}`);
      stats.skipped += 1;
      continue;
    }

    const data = fs.readFileSync(absPath);

    if (isBinaryBuffer(data)) {
      console.log(`Skipping (binary or non-text): ${rel}`);
      stats.skipped += 1;
      continue;
    }

    stats.merged += 1;

    if (options.dryRun) {
      console.log(`[DRY-RUN] Would merge: ${rel}`);
      continue;
    }

    console.log(`Merging: ${rel}`);
    chunks.push(buildFileBlock(rel, data.toString('utf8')));
  }

  return {
    text: chunks.join(''),
    stats,
  };
}

function findCleanSplitPoint(text, start, target, minimumRemainingCharacters) {
  const lastAllowedSplit = Math.max(start + 1, text.length - minimumRemainingCharacters);
  const safeTarget = Math.min(Math.max(target, start + 1), lastAllowedSplit);

  const previousHeader = text.lastIndexOf('\n=== ', safeTarget);

  if (previousHeader > start) {
    return previousHeader + 1;
  }

  const nextHeader = text.indexOf('\n=== ', safeTarget);

  if (nextHeader !== -1 && nextHeader <= lastAllowedSplit) {
    return nextHeader + 1;
  }

  const previousNewLine = text.lastIndexOf('\n', safeTarget);

  if (previousNewLine > start && !endsWithOnlyAFileHeader(text.slice(start, previousNewLine + 1))) {
    return previousNewLine + 1;
  }

  const nextNewLine = text.indexOf('\n', safeTarget);

  if (nextNewLine !== -1 && nextNewLine <= lastAllowedSplit) {
    const candidate = nextNewLine + 1;

    if (endsWithOnlyAFileHeader(text.slice(start, candidate))) {
      const nextContentLine = text.indexOf('\n', candidate);

      if (nextContentLine !== -1 && nextContentLine <= lastAllowedSplit) {
        return nextContentLine + 1;
      }
    }

    return candidate;
  }

  return safeTarget;
}

function endsWithOnlyAFileHeader(text) {
  return /^\s*=== .+ ===\n$/.test(text);
}

function splitTextIntoPieces(text, totalPieces) {
  if (totalPieces === 1) {
    return [text];
  }

  if (text.length === 0) {
    return Array.from({ length: totalPieces }, () => '');
  }

  if (text.length < totalPieces) {
    return [text, ...Array.from({ length: totalPieces - 1 }, () => '')];
  }

  const pieces = [];
  let start = 0;

  for (let index = 1; index < totalPieces; index += 1) {
    const target = Math.round((text.length * index) / totalPieces);
    const splitAt = findCleanSplitPoint(text, start, target, 0);

    pieces.push(text.slice(start, splitAt));
    start = splitAt;
  }

  pieces.push(text.slice(start));

  return pieces;
}

function writeOutputFiles(outputFile, text, options) {
  let gitSha = 'unknown';
  try {
    gitSha = execSync('git rev-parse HEAD', { encoding: 'utf8' }).trim();
  } catch (e) {}

  let type = 'WORKSPACE AGGREGATE (NON-CANONICAL)';
  let warning = '⚠️  WARNING: This is a debug/aggregate dump and may include legacy or archived projects.\n';
  
  if (options.canonical) {
    type = 'AGENT HARNESS CANONICAL TRUTH DUMP';
    warning = '✅ CERTIFIED: This is the canonical harness truth source.\n';
  }

  const header = `============================================================
${type}
Commit: ${gitSha}
Timestamp: ${new Date().toISOString()}
${warning}============================================================\n\n`;

  const final_text = header + text;

  if (options.pieces === 1) {
    fs.writeFileSync(outputFile, final_text, 'utf8');

    return [outputFile];
  }

  return splitTextIntoPieces(final_text, options.pieces).map((pieceText, index) => {
    const piecePath = outputPiecePath(outputFile, index + 1, options.pieces);

    fs.writeFileSync(piecePath, pieceText, 'utf8');

    return piecePath;
  });
}

function printStartupReport(rootDir, outputFile, options) {
  console.log(`Scanning directory: ${rootDir}`);
  console.log(`Output file: ${outputFile}`);
  console.log(`Output mode: ${options.pieces === 1 ? 'single file' : `${options.pieces} pieces`}`);
  console.log('Ignoring directory names:');

  for (const dir of [...DEFAULT_IGNORED_DIRECTORY_NAMES].sort(compareStrings)) {
    console.log(`- ${dir}`);
  }

  if (options.exceptRules.size > 0) {
    console.log('Ignoring --except rules:');

    for (const rule of [...options.exceptRules].sort(compareStrings)) {
      console.log(`- ${rule}`);
    }
  }

  console.log('----------------------------------------');
}

function printFinalReport(stats, writtenFiles, options) {
  console.log('----------------------------------------');
  console.log('Done!');
  console.log(`Merged files : ${stats.merged}`);
  console.log(`Skipped files: ${stats.skipped}`);

  if (options.dryRun) {
    console.log(`Output mode  : ${options.pieces === 1 ? 'single file' : `${options.pieces} pieces`}`);
    return;
  }

  if (writtenFiles.length === 1) {
    console.log(`Output file  : ${writtenFiles[0]}`);
    return;
  }

  console.log('Output files :');

  for (const writtenFile of writtenFiles) {
    console.log(`- ${writtenFile}`);
  }
}

const options = parseOptions(process.argv.slice(2));
const rootDir = resolveRootDirectory(options.targetArg);
const targetBasename = path.basename(rootDir);
const packageName = readPackageName(rootDir);
const outputFile = outputFileFor(rootDir, targetBasename, packageName);
const legacyOutputFile = path.join(rootDir, `${targetBasename}.txt`);
const outputFamilies = [
  outputFamilyFor(outputFile),
  outputFamilyFor(legacyOutputFile),
];

printStartupReport(rootDir, outputFile, options);

const files = collectTextFiles(rootDir, options, outputFamilies);
const result = mergeFiles(rootDir, files, options);
const writtenFiles = options.dryRun
  ? []
  : writeOutputFiles(outputFile, result.text, options);

printFinalReport(result.stats, writtenFiles, options);
NODE