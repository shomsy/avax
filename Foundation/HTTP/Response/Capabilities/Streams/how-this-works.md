---
title: Streams-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# Streams How This Works

## What this folder is

This folder owns creation, wrapping, file opening, and rewinding of response streams.

## Real commands or triggers that reach this folder

- body normalization
- download responses
- request-side code that needs a PSR-7 stream implementation
- emitter body output

## Exact upstream handoffs

- `Capabilities/Body/NormalizeResponseBody.php` -> `ResponseStreamFactory.php`
- `Flows/BuildResponse/BuildFileDownloadResponse.php` -> `OpenFileStream.php`
- `Flows/EmitResponse/EmitResponseBody.php` -> `RewindStream.php`

## The simplest story

- callers ask for a stream from a string, file, or resource
- this folder returns a concrete PSR-7 stream

## Debug first

- start here when body content truncates, file streams fail, or emitted content starts mid-stream

## What to remember

- stream creation lives here so the rest of the component stays PSR-focused
