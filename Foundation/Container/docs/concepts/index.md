# Concepts

## What This Folder Represents

This folder is your “cross-cutting” chapter: ideas that show up everywhere in the Container, but don’t belong to a
single class. You come here when you’re trying to understand the system’s vocabulary and mental model: lifetimes,
scopes, resolution flow, policies, and the boundary between “creating” and “injecting”.

### For Humans: What This Means (Summary)

When you’re lost in a big codebase, it’s usually because you’re missing one or two big ideas — not because you forgot a
method name. This folder is where those big ideas live, so you can stop guessing and start reasoning.

## What Belongs Here

- Concepts that are used by many folders and many classes
- “How the container thinks” explanations (flow, phases, trade-offs)
- Links to concrete classes that implement each concept

### For Humans: What This Means (Belongs)

If you keep running into the same word in multiple places (“lifetime”, “scope”, “pipeline”), this is where you should
learn what it really means in this codebase.

## What Does NOT Belong Here

- API documentation for a single class (that belongs in the file’s `.md`)
- How-to guides for a specific application or framework integration
- Release notes or changelogs

### For Humans: What This Means (Not Belongs)

This isn’t a “manual for a screen” or “docs for a command”. It’s the “why and how to think” layer.

## How Files Collaborate

Each concept document explains a domain of behavior (like lifetimes or scopes) and links you to the concrete classes
that implement it. You’ll usually read a concept first, then jump to class docs to learn the exact APIs.

### For Humans: What This Means (Collaboration)

Concepts are the map. Class docs are the street view. Use both: map first, street view second.
