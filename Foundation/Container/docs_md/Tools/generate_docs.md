# generate_docs.php

## Quick Summary
This script walks the project tree, builds a navigation model, and emits documentation skeletons so you can fill in book-like content later. It exists to keep documentation structure in lockstep with the filesystem, eliminating manual folder-by-folder setup and reducing the chance of missing files. The complexity of mirroring directories, generating anchors, and wiring navigation disappears because the script does it deterministically.

### For Humans: What This Means
You run one script and instantly get a ready-to-edit set of docs that match the codebase. No more creating folders or index files by hand—the script does the boring setup so you can focus on writing.

## Terminology
- **Recursive traversal**: Systematically visiting every folder and file in depth-first order. Here it ensures no PHP file is missed when generating docs.
- **Navigation tree**: A data structure that maps folders to their subfolders and files. The script builds this to render sidebars and links consistently.
- **Relative linking**: Calculating links based on the current file’s path to avoid broken navigation when moved. The `rel()` helper does this math.
- **Dual-layer sections**: Sections that pair a technical explanation with a plain-language translation. The script prebuilds placeholders to enforce this style.
- **Skeleton generation**: Creating starter docs with required sections and anchors but without final prose. This keeps structure consistent while inviting content authors to fill details.

### For Humans: What This Means
These terms are the moving parts of the generator. It crawls everything, maps relationships, builds correct links, and pre-populates sections that force clear explanations.

## Think of It
Treat this script like a contractor that frames a house. It doesn’t finish the interior, but it builds the walls, rooms, and hallways in the right places so you can focus on finishing touches.

### For Humans: What This Means
You get the framing done for you. The layout matches the blueprint (your folders), and you just add the details.

## Story Example
Before this script, documenting a new folder meant manually creating indexes, writing boilerplate sections, and worrying about links. Now you run `php tools/generate_docs.php src docs_md`, and the entire structure appears with placeholders and correct navigation. You spend time on content, not scaffolding.

### For Humans: What This Means
Instead of wasting an afternoon setting up files, you run one command and jump straight into writing.

## For Dummies
1) Point the script at your source folder and desired output folder.
2) It walks every directory, skipping none.
3) It writes an index for each folder and a skeleton for each PHP file with dual-layer prompts.
4) It wires navigation so links work out of the box.
5) You open the generated Markdown and replace the placeholders with real content.

Common misconceptions:
- “It documents code automatically.” It scaffolds; you still supply the prose.
- “It only works for certain frameworks.” It just walks files; it’s framework-agnostic.
- “It’s optional.” Without it, keeping docs in sync is error-prone.

### For Humans: What This Means
This is a setup helper, not an AI writer. It guarantees structure and links so you can write safely.

## How It Works (Technical)
- Discovers all directories and PHP files via `RecursiveDirectoryIterator` and builds a unique, sorted list.
- Constructs a navigation tree keyed by directories, associating child dirs and files.
- Uses `rel()` to compute relative paths between the current document and targets.
- Generates folder `index.md` files with placeholder dual-layer content.
- Generates file docs with required sections plus a “Methods” list derived from public/protected methods.
- Keeps output in portable Markdown so it can be converted later.

### For Humans: What This Means
It walks the tree, remembers where everything lives, and then writes matching doc stubs with links that already work.

## Architecture Role
The script lives in `Tools/` because it’s a development utility, not runtime logic. It depends on PHP’s filesystem iterators and minimal project knowledge, and other tooling (like doc styles) depends on its output to stay consistent.

### For Humans: What This Means
This is part of the tooling toolkit: it prepares docs so the rest of the team can write them without thinking about structure.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: rel

#### Technical Explanation
Computes a relative path from one location to another by comparing path segments and adding `../` prefixes as needed. Keeps navigation links stable even when rendered from nested folders.

##### For Humans: What This Means
It figures out how many folders to step back before walking to the target, so links work no matter where the current page sits.

##### Parameters
- `string $from`: The current directory path.
- `string $to`: The target path to link to.

##### Returns
- `string`: The computed relative path.

##### Throws
- None.

##### When to Use It
Use for any navigation link between generated docs.

##### Common Mistakes
Passing already-relative paths that include `../` can double-walk; always pass clean paths.

### Method: makeNav

#### Technical Explanation
Builds Markdown navigation by walking the directory tree representation and emitting sections per folder with file links.

##### For Humans: What This Means
It renders the clickable menu that lets you move between docs.

##### Parameters
- `string $currentDir`: Directory of the document being generated.
- `array $tree`: Directory tree containing subdirectories and files.

##### Returns
- `string`: Markdown for a navigation section.

##### Throws
- None.

##### When to Use It
During generation of any page that needs sidebar navigation.

##### Common Mistakes
Providing an incomplete tree results in missing links.

### Method: wrapHtml

#### Technical Explanation
Wraps provided title, metadata, navigation, and content in a single Markdown document structure.

##### For Humans: What This Means
It takes page pieces and turns them into a complete Markdown document.

##### Parameters
- `string $title`: Page title.
- `string $lead`: Lead text/hero subtitle.
- `string $meta`: Metadata block.
- `string $nav`: Navigation Markdown.
- `string $content`: Main body Markdown.
- `string $relStyles`: Relative path to CSS.
- `string $homeRel`: Relative path to the docs root.

##### Returns
- `string`: Full Markdown document as a string.

##### Throws
- None.

##### When to Use It
Whenever writing a generated Markdown page to disk.

##### Common Mistakes
Forgetting to adjust `relStyles` for nested directories leads to broken styling.

### Method: section

#### Technical Explanation
Creates a section wrapper with a heading and provided Markdown body to keep layout consistent across generated pages.

##### For Humans: What This Means
It stamps out the standard card-style sections used throughout the docs.

##### Parameters
- `string $title`: Section heading.
- `string $body`: Inner Markdown content.

##### Returns
- `string`: Wrapped Markdown section.

##### Throws
- None.

##### When to Use It
Any time the generator adds a new content section.

##### Common Mistakes
Passing unescaped user input can break generated Markdown and navigation.

### Method: dualLayer

#### Technical Explanation
Appends a “For Humans: What This Means” block after a technical explanation, enforcing dual-layer documentation across sections in Markdown.

##### For Humans: What This Means
It automatically inserts a friendly translation prompt beneath technical text.

##### Parameters
- `string $tech`: Technical Markdown snippet.

##### Returns
- `string`: Combined technical and human-friendly Markdown block.

##### Throws
- None.

##### When to Use It
Whenever generating sections that need dual-layer explanations.

##### Common Mistakes
Forgetting to replace placeholder text leaves TODOs visible in canonical docs.

### Method: extractMethods

#### Technical Explanation
Parses PHP code to find public/protected method names using regex, skipping constructors, so generated docs can include method anchors.

##### For Humans: What This Means
It scans files to list methods that need documentation anchors.

##### Parameters
- `string $filePath`: Absolute path to the PHP file being scanned.

##### Returns
- `array<int, string>`: List of method names found.

##### Throws
- None.

##### When to Use It
Before generating per-file doc skeletons to pre-list methods.

##### Common Mistakes
Regex may miss methods with unusual formatting; keep code style consistent.

### Method: makeFileContent

#### Technical Explanation
Produces the Markdown content for a PHP file doc: all required sections plus optional Methods list populated from `extractMethods`. Uses dual-layer prompts for human translation.

##### For Humans: What This Means
It builds the skeleton content for a file’s documentation page.

##### Parameters
- `string $fileRel`: Path to the file relative to the source root.
- `string $filePath`: Absolute path to the file.

##### Returns
- `string`: Markdown for the file documentation body.

##### Throws
- None.

##### When to Use It
During generation of each PHP file’s documentation page.

##### Common Mistakes
If `extractMethods` misses methods, anchors will be incomplete; review generated output.

### Method: makeFolderContent

#### Technical Explanation
Generates placeholder Markdown for a folder’s index page, prompting for conceptual purpose, inclusion/exclusion rules, and collaboration notes with dual-layer format.

##### For Humans: What This Means
It creates the starting content for folder-level docs so authors remember to explain structure.

##### Parameters
- `string $dirRel`: Folder path relative to the source root (empty string for root).

##### Returns
- `string`: Markdown for the folder documentation body.

##### Throws
- None.

##### When to Use It
When writing index pages for each folder during generation.

##### Common Mistakes
Leaving placeholders unchanged reduces doc quality; always replace with real explanations.

## Risks, Trade-offs & Recommended Practices
- **Risk: Stale placeholders**. Generated docs remain skeletons unless filled in. Commit only after replacing TODOs.
- **Risk: Missing edge-case methods**. Regex parsing can miss atypical method signatures; review output manually.
- **Trade-off: generator output expectations**. Keep the generator aligned with your canonical Markdown rules so output stays portable and future-proof.
- **Practice: Regenerate after structural changes**. Run the script when files/folders are added or moved to keep docs aligned.
- **Practice: Verify relative links**. Spot-check navigation from nested folders to avoid broken styles.

### For Humans: What This Means
The generator saves time but can leave rough edges. Always replace placeholders, double-check methods, and rerun after structure changes so docs stay trustworthy.

## Related Files & Folders
- `docs_md/Tools/index.md`: Explains the Tools workspace where this script lives.
- `docs_md/Tools/Console/index.md`: Documents the CLI utilities that often accompany doc generation.
- `docs/styles.css`: Shared styling the script copies into outputs.

### For Humans: What This Means
Read the Tools folder docs for context, check the Console utilities for related commands, and know which styles the generator expects.
