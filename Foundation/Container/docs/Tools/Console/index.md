# Tools/Console

## What This Folder Represents

This folder hosts command-line utilities that operate on the container: diagnostics, inspection, cache management, compilation, and service lifecycle administration. It exists to give operators and developers scripted, repeatable access to container internals without changing runtime code.

### For Humans: What This Means (Summary)

These are your command-line controls for the container—buttons and dashboards you can run from a terminal to see health, manage services, and rebuild caches.

## What Belongs Here

Symfony Console commands and supporting CLI entry points that inspect, manage, or prepare the container (diagnose, inspect services, compile caches, clear caches, export/import definitions).

### For Humans: What This Means (Belongs)

If it’s a terminal command that helps you understand or control the container, it should live here.

## What Does NOT Belong Here

Runtime container logic, HTTP controllers, or business features. Avoid putting application-specific scripts or one-off shell helpers; keep those elsewhere.

### For Humans: What This Means (Not Belongs)

Don’t mix app code or random scripts here—only CLI tools that manage the container belong.

## How Files Collaborate

Each command focuses on a slice of container operations: inspection commands pull diagnostics and prototypes, management commands handle definitions, cache/compile commands maintain performance. Together they provide full operational coverage and share common container services.

### For Humans: What This Means (Collaboration)

Think of these commands as a toolkit: one for looking, one for cleaning, one for building, one for managing inventory. They share the same container data to give you a complete operational picture.
