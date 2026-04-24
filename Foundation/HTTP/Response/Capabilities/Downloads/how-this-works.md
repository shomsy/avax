---
title: Downloads-how-this-works
owner: foundation-http
last_reviewed: 2026-04-24
classification: internal
---

# Downloads How This Works

## What this folder is

This folder owns download-specific HTTP details such as content disposition and media-type detection.

## Real commands or triggers that reach this folder

- `BuildFileDownloadResponse`

## Exact upstream handoffs

- `Flows/BuildResponse/BuildFileDownloadResponse.php` -> `DetectDownloadMediaType.php`

## The simplest story

- a file path enters the download builder
- this folder provides safe headers for download delivery

## Debug first

- start here when download names or media types are wrong

## What to remember

- file IO stays in the stream layer, download header rules stay here
