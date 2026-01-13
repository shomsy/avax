# Features/Core/Utils

## What This Folder Represents

General-purpose utility helpers used across the Container component. They exist to avoid repeating small, common logic
across many files.

### For Humans: What This Means (Represent)

These are the container’s helper tools—small functions packaged as classes.

## What Belongs Here

Utility classes like `ArrayTools` and `StrTools`.

### For Humans: What This Means (Belongs)

If it’s a small helper that multiple parts of the container use, it belongs here.

## What Does NOT Belong Here

Business rules or large subsystems.

### For Humans: What This Means (Not Belongs)

Keep utilities small and generic.

## How Files Collaborate

Utilities are called from many layers (features, kernel, tools) without introducing tight coupling.

### For Humans: What This Means (Collaboration)

These helpers support the whole component without owning any big responsibilities.
