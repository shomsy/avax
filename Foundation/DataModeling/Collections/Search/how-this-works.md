# Search

Search and match operations find items in collections.

## Purpose

This folder contains search operations including exact match, partial match, and fuzzy search.

## Classes

| Class                         | Responsibility             |
|-------------------------------|----------------------------|
| `ContainsValue.php`           | Check if value exists      |
| `SearchValue.php`             | Find index of value        |
| `MatchTextPartially.php`      | Partial text match         |
| `MatchTextBySimilarity.php`   | Similarity match           |
| `MatchTextFuzzily.php`        | Fuzzy text match           |
| `MatchTextByLevenshtein.php`  | Levenshtein distance match |
| `MatchTextBySortedTokens.php` | Sorted token match         |
| `MatchTextPhonetically.php`   | Phonetic match             |
| `MatchTextByPattern.php`      | Regex pattern match        |