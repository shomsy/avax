---
title: WebSocket-how-this-works
owner: realtime
last_reviewed: 2026-04-30
classification: internal
---

# WebSocket How This Works

## What this folder is

This folder owns the in-process WebSocket model used by Avax for channel broadcasts, user broadcasts, presence tracking,
and client helper generation.

## Real commands or triggers that reach this folder

Realtime entrypoints call `WebSocketServer::connect()`, `WebSocketServer::broadcast()`, `WebSocketServer::toChannel()`,
and `WebSocketServer::toUser()`.

## Exact upstream handoffs

`Realtime` is the public doorway. It delegates broadcast behavior to `WebSocketServer` and channel behavior to the
channel manager.

## Failure behavior

Sending to an unknown connection returns `false`; broadcasting to an empty channel returns `0`. Presence membership is
isolated in memory and can be reset in tests.
