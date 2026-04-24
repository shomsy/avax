---
title: Redirects-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# Redirects How This Works

## What this folder is

This folder owns redirect target validation and redirect status normalization.

## Real commands or triggers that reach this folder

- `BuildRedirectResponse`

## Exact upstream handoffs

- `Flows/BuildResponse/BuildRedirectResponse.php` -> `ValidateRedirectTarget.php`

## The simplest story

- redirect builders hand in a target and status
- this folder rejects invalid targets or invalid redirect codes early

## Debug first

- start here when redirects accept invalid URLs or wrong status codes

## What to remember

- redirect rules are isolated instead of hidden inside a generic factory
