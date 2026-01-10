# generate_docs.php

## Quick Summary

This script walks the project tree, builds a navigation model, and emits documentation skeletons so you can fill in book-like content later. It exists to keep documentation structure in lockstep with the filesystem, eliminating manual folder-by-folder setup and reducing the chance of missing files.

### For Humans: What This Means (Summary)

You run one script and instantly get a ready-to-edit set of docs that match the codebase. No more creating folders or index files by hand—the script does the boring setup so you can focus on writing.

## Terminology (MANDATORY, EXPANSIVE)

- **Recursive traversal**: Systematically visiting every folder and file in depth-first order.
- **Navigation tree**: A data structure that maps folders to their subfolders and files.
- **Relative linking**: Calculating links based on the current file’s path.
- **Dual-layer sections**: Sections that pair a technical explanation with a plain-language translation.
- **Skeleton generation**: Creating starter docs with required sections and anchors.

### For Humans: What This Means (Terminology)

These terms are the moving parts of the generator. It crawls everything, maps relationships, builds correct links, and pre-populates sections that force clear explanations.

## Think of It

Treat this script like a contractor that frames a house. It doesn’t finish the interior, but it builds the walls, rooms, and hallways in the right places so you can focus on finishing touches.

### For Humans: What This Means (Analogy)

You get the framing done for you. The layout matches the blueprint (your folders), and you just add the details.

## Story Example

Before this script, documenting a new folder meant manually creating indexes, writing boilerplate sections, and worrying about links. Now you run `php tools/generate_docs.php`, and the entire structure appears with placeholders and correct navigation.

### For Humans: What This Means (Story)

Instead of wasting an afternoon setting up files, you run one command and jump straight into writing.

## For Dummies

1) Point the script at your source folder and desired output folder.
2) It walks every directory, skipping none.
3) It writes an index for each folder and a skeleton for each PHP file.
4) It wires navigation so links work out of the box.
5) You open the generated Markdown and replace the placeholders.

### For Humans: What This Means (Walkthrough)

This is a setup helper, not an AI writer. It guarantees structure and links so you can write safely.

## How It Works (Technical)

- Discovers all directories and PHP files via `RecursiveDirectoryIterator`.
- Constructs a navigation tree keyed by directories.
- Uses `rel()` to compute relative paths between documents.
- Generates folder `index.md` files with placeholder content.

### For Humans: What This Means (Technical)

It walks the tree, remembers where everything lives, and then writes matching doc stubs with links that already work.

## Architecture Role

The script lives in `Tools/` because it’s a development utility, not runtime logic. It depends on PHP’s filesystem iterators and minimal project knowledge.

### For Humans: What This Means (Architecture)

This is part of the tooling toolkit: it prepares docs so the rest of the team can write them without thinking about structure.

## Methods

This section documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods Summary)

When you’re trying to use or debug this file, this is the part you’ll come back to.

### Method: rel

#### Technical Explanation (rel)

Computes a relative path from one location to another by comparing path segments.

##### For Humans: What This Means (rel)

It figures out how many folders to step back before walking to the target.

### Method: makeNav

#### Technical Explanation (makeNav)

Builds Markdown navigation by walking the directory tree representation.

##### For Humans: What This Means (makeNav)

It renders the clickable menu that lets you move between docs.

### Method: wrapHtml

#### Technical Explanation (wrapHtml)

Wraps provided title, metadata, navigation, and content in a single Markdown document structure.

##### For Humans: What This Means (wrapHtml)

It takes page pieces and turns them into a complete Markdown document.

### Method: section

#### Technical Explanation (section)

Creates a section wrapper with a heading and provided Markdown body.

##### For Humans: What This Means (section)

It stamps out the standard card-style sections used throughout the docs.

### Method: dualLayer

#### Technical Explanation (dualLayer)

Appends a “For Humans: What This Means” block after a technical explanation.

##### For Humans: What This Means (dualLayer)

It automatically inserts a friendly translation prompt beneath technical text.

### Method: extractMethods

#### Technical Explanation (extractMethods)

Parses PHP code to find public/protected method names using regex.

##### For Humans: What This Means (extractMethods)

It scans files to list methods that need documentation anchors.

### Method: makeFileContent

#### Technical Explanation (makeFileContent)

Produces the Markdown content for a PHP file doc utilizing dual-layer prompts.

##### For Humans: What This Means (makeFileContent)

It builds the skeleton content for a file’s documentation page.

### Method: makeFolderContent

#### Technical Explanation (makeFolderContent)

Generates placeholder Markdown for a folder’s index page with dual-layer format.

##### For Humans: What This Means (makeFolderContent)

It creates the starting content for folder-level docs.

## Risks, Trade-offs & Recommended Practices

- **Risk: Stale placeholders**. Generated docs remain skeletons unless filled in.
- **Risk: Missing edge-case methods**. Regex parsing can miss atypical signatures.
- **Practice: Regenerate after structural changes**. Run the script when files are moved.

### For Humans: What This Means (Risks Summary)

The generator saves time but can leave rough edges. Always replace placeholders and rerun after structure changes.

## Related Files & Folders

- `docs/Tools/index.md`: Explains the Tools workspace.
- `docs/Tools/Console/index.md`: Documents the CLI utilities.
- `docs/styles.css`: Shared styling the script copies.

### For Humans: What This Means (Relationships)

Read the Tools folder docs for context and know which styles the generator expects.
