# `language-specific/` — Foundation/Container

This component is a PHP library and should follow the local PHP profile.

## Current Stack

- PHP 8.3+
- `declare(strict_types=1);`
- PSR-12-style formatting
- named arguments and constructor promotion where they improve clarity
- no framework overlay is assumed at this layer

## Use This Folder For

1. repo-local PHP conventions that are narrower than reusable governance profiles
2. notes about static analysis, autoloading, and library-specific constraints
3. temporary local overlays if the PHP profile needs project-specific adaptation

## Generalization Rule

If a rule becomes reusable across unrelated PHP repositories, move it into
`.agents/.rules/governance/profiles/languages/php.md`.
