---
title: Read-how-this-works
owner: foundation-data
last_reviewed: 2026-04-24
classification: internal
---

# Read

Read operations retrieve values from collections.

## Purpose

This folder contains getter operations. Each class owns one exact action.

## Classes

| Class                 | Responsibility                 |
|-----------------------|--------------------------------|
| `ReadValue.php`       | Get value by key               |
| `ReadValueByPath.php` | Get value by dot notation path |
| `HasValue.php`        | Check if key exists            |
| `ReadFirstValue.php`  | Get first item                 |
| `ReadLastValue.php`   | Get last item                  |
| `ReadKeys.php`        | Get all keys                   |
| `ReadValues.php`      | Get all values                 |
| `PluckValues.php`     | Extract values by key          |
| `ReadOnlyKeys.php`    | Read without specified keys    |
| `ReadExceptKeys.php`  | Read excluding specified keys  |