# FilePrototypeCache

## Quick Summary

- This file is the **Physical Archive** for class blueprints—it saves them to your hard drive.
- It exists to leverage the incredible speed of PHP's **OPcache** by storing blueprints as standard PHP code.
- It removes the performance bottleneck of "First-load" analysis by making sure the container only has to "Think" once per class.

### For Humans: What This Means (Summary)

This is the **Hardcover Library**. When the container learns how to build a class, this service writes it down in a PHP book and puts it on a shelf (a folder on your disk). The next time the container needs that info, it doesn't just read the book—it lets PHP's own high-speed memory systems (OPcache) keep the book "Open" so the information can be retrieved almost instantly.

## Terminology (MANDATORY, EXPANSIVE)

- **Atomic Rename**: A file operation where a file is renamed instantly. If the rename fails, the destination file is never touched.
  - In this file: Handled by `rename()` after `file_put_contents()` to a `.tmp` file.
  - Why it matters: Prevents "Race Conditions"—situations where one user is trying to read a blueprint while another is halfway through writing it.
- **Opcode Caching (OPcache)**: A core PHP feature that stores the "Compiled" version of PHP files in RAM.
  - In this file: Leveraged by using `require` on files generated with `var_export()`.
  - Why it matters: By saving blueprints as PHP code, we trick PHP into keeping our data in super-fast RAM automatically.
- **Locking (LOCK_EX)**: Ensuring that only one process can write to a file at a time.
  - In this file: Used during the temporary file creation.
  - Why it matters: Prevents multiple processes from garbling each other's data during high traffic.
- **Path Mapping**: Translating a complex class name (with backslashes) into a safe filename.
  - In this file: Handled by `getPath()`.
  - Why it matters: Filesystems don't like backslashes in filenames. We replace them with underscores so the file can be saved safely.

### For Humans: What This Means (Terminology)

The File Cache uses **Atomic Renames** (Safe switches) to leverage **Opcode Caching** (Super-fast RAM) while using **Locking** (Queueing) and **Path Mapping** (Safe naming).

## Think of It

Think of a **Set of Recipe Cards in a Kitchen**:

1. **The Cards (Blueprints)**: The technical instructions for a dish.
2. **The Permanent Marker (`var_export`)**: Writing the card clearly so it never fades.
3. **The Box (The Directory)**: Where all the cards are stored.
4. **The Quick Flip (OPcache)**: Because you used standard cards, the chef (PHP) remembers the recipes by heart after reading them once.

### For Humans: What This Means (Analogy)

It’s like writing down your notes and then having a photographic memory of them. The "Notes" are the files, and the "Photographic Memory" is what PHP does for us for free.

## Story Example

You have a server running 20 different websites.

1. When Website A starts, it analyzes its `UserController`. The **FilePrototypeCache** writes `UserController.php` to the `storage/cache` folder.
2. Because it's a standard PHP file, the server's **OPcache** sees it and says "I'll keep this in RAM".
3. For the next 10,000 users, the container doesn't even have to touch the "Hard Drive". It just asks PHP to `require` the file, and PHP gives it the object instantly from RAM.
4. If you deploy new code, your deployment script calls `clear()`, the old files are deleted, and the container begins re-learning.

### For Humans: What This Means (Story)

It makes the container as fast as "Hard-coded" code. Even though the blueprints are "Dynamic", they are stored in a way that allows PHP to treat them as part of the core engine.

## For Dummies

Imagine you have a big textbook.

1. **Reading**: You have to find the page, read the paragraph, and understand it. (Analysis).
2. **Bookmark**: You write a "Cheat Sheet" and tape it to the cover of the book. (This class).
3. **Next Time**: You don't open the book. You just look at the cover.
4. **Safety**: You write the cheat sheet on a separate piece of paper first, and only tape it to the cover *after* you're sure you didn't smudge the ink. (Atomic Rename).

### For Humans: What This Means (Walkthrough)

It's a "Cheat Sheet Factory". It takes hard information and turns it into easy-to-read labels.

## How It Works (Technical)

`FilePrototypeCache` is an "AOT-Friendly" persistence engine:

1. **Write Logic**: It uses `var_export($prototype, true)` to generate a valid PHP string that returns the object. It writes this to a `.tmp` file and then performs a `rename()`. This is the industry-standard way to perform "Atomic Writes" in PHP.
2. **Read Logic**: It uses `require $path`. Because blueprints use `__set_state`, PHP can reconstruct the entire object tree (including nested Methods and Parameters) in a single operation.
3. **Filenaming**: It converts `Avax\Services\MyClass` into `Avax_Services_MyClass.php`. This ensures unique files for every class and prevents filename collisions.
4. **Error Resilience**: The `get()` method is wrapped in a `try/catch`. If a cache file is corrupted or deleted halfway through, it simply returns `null` and lets the container re-analyze the class instead of crashing the site.

### For Humans: What This Means (Technical)

It is a "PHP-Generating-PHP" system. It writes code that describes your code, which allows the server to optimize the "Description" just as much as the "Actual Code".

## Architecture Role

- **Lives in**: `Features/Think/Cache`
- **Role**: Concrete Filesystem Persistence implementation.
- **Goal**: To provide production-grade speed with filesystem-backed reliability.

### For Humans: What This Means (Architecture)

It is the "Hard drive" of the Intelligence Layer.

## Methods

### Method: set(string $class, ServicePrototype $prototype)

#### Technical Explanation: set

Exports the object to PHP code and performs an atomic swap to prevent corruption.

#### For Humans: What This Means (set)

"Write down the plan in a PHP file safely so we can use it later."

### Method: get(string $class)

#### Technical Explanation: get

Includes the PHP file. Leverages OPcache for near-zero latency.

#### For Humans: What This Means (get)

"Ask PHP to give us back the plan we saved earlier."

### Method: clear()

#### Technical Explanation: clear

Iterates through the cache directory and deletes all files matched by `*.php`.

#### For Humans: What This Means (clear)

"Empty the folder of all saved blueprints."

## Risks & Trade-offs

- **Disk Space**: If you have thousands of classes, the cache folder might grow to several megabytes. This is usually fine on modern servers.
- **Permissions**: The folder must be writable by the web server (e.g. `www-data`). If it's not, the container will slow down significantly because it can't save what it learns.

### For Humans: What This Means (Risks)

"Check the locks". If the web server doesn't have permission to write to this folder, the cache can't work, and your site might be 10x slower than it should be.

## Related Files & Folders

- `PrototypeCache.php`: The interface this class follows.
- `ServicePrototype.php`: The data being saved.
- `OPcache (PHP Internal)`: The system that makes this class "Magic".

### For Humans: What This Means (Relationships)

The **Interface** says what to do, this **Class** does it using **Files**, and **PHP** makes it super fast.
