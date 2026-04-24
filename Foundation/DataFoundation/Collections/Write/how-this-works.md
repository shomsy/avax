---
title: Write-how-this-works
owner: foundation-data
last_reviewed: 2026-04-24
classification: internal
---

# Write

Write operations modify collections immutably.

## Purpose

This folder contains setter operations. Each class owns one exact action and returns a new collection with the change
applied.

## Classes

| Class                | Responsibility            |
|----------------------|---------------------------|
| `PutValue.php`       | Set value by key          |
| `PutValueByPath.php` | Set value by dot notation |
| `AppendValue.php`    | Append value to end       |
| `PrependValue.php`   | Prepend value to start    |
| `ForgetValue.php`    | Remove value by key       |
| `PullValue.php`      | Remove and return value   |
| `MergeValues.php`    | Merge arrays              |
| `ReplaceValues.php`  | Replace at index          |