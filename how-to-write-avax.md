First and foremost, strictly follow AGENTS.md and everything inside the .agents directory. That is the core of the AvaX
SDLC and governance system.

You must strictly follow every single how-to-*.md rule while working. Every rule means every rule, without exceptions. I
want enterprise-grade quality, nothing less.

Everything you do must follow:

* TODO.md in the root of the AvaX repository,
* EXECUTION.md,
* all how-to-*.md governance documents,
* all active V1/V2/V3/V4 development plans,
* and every architecture/code-quality rule defined across the repository.

Do not remove or skip implementation details just because you personally disagree with them. Everything must be
implemented correctly so AvaX becomes:

* READY TO USE
* PRODUCTION READY
* architecture-grade
* runtime-safe
* governance-driven

Maintain visible progress tracking inside TODO.md at all times.
TODO.md represents the current project truth and active working state.

Requirements for TODO.md:

* clearly mark DONE, WIP, PARTIAL, BLOCKED items,
* preserve implementation history,
* allow another agent to continue exactly where you stopped,
* use checkbox-based tracking whenever possible.

Before implementing anything:

* search tooling/ for existing scripts or automation that can help,
* reuse tooling if it already exists,
* improve tooling if possible,
* optimize repetitive work through automation.

You are encouraged to create additional tooling when useful:

* Rector custom rules,
* PHPStan extensions,
* architecture validation scripts,
* naming validators,
* governance enforcement tooling,
* auto-fix tooling,
* repository normalization tooling.

You may implement tooling in:

* PHP,
* Go,
* Rust,
* Node.js,
* Python,
  or any language that improves execution speed and workflow efficiency.

PHP is not a holy grail for tooling.

If external libraries or dependencies are introduced:

* document them clearly,
* explain why they were added,
* explain what problem they solve,
* update project documentation appropriately.

At all times, continuously verify:

* Does the implementation follow TODO.md?
* Does it follow EXECUTION.md?
* Does it follow all how-to-*.md governance?
* Does it follow the active development plan exactly?
* Does the actual project tree match the planned project tree 1:1?
* Does the implementation violate any naming, architecture, OOP, runtime, testing, or governance rule?

If a development plan conflicts with how-to-*.md governance:

* stop,
* report the conflict clearly,
* explain exactly why the plan violates governance,
* propose a corrected version.

Recovery instructions:

* Use EVIDENCE/archive/avax-backup.txt as recovery source.
* Also inspect:

    * main branch history,
    * Framework.txt,
    * Components.txt,
    * historical architecture files,
    * legacy implementations,
    * archived recovery evidence.

These files must NEVER be deleted.

You are allowed and encouraged to:

* recover previous ideas,
* reuse older implementations,
* modernize older concepts,
* port legacy behavior into current architecture,
* extract reusable runtime/capability ideas from historical code.

However:

* all recovered implementations must follow current governance,
* all recovered code must be adapted to current V1/V2/V3/V4 plans,
* all recovered code must follow current naming and architecture standards.

Do not wait for permission after each task.
Continue executing the next allowed action automatically.
You are trusted to continue sequentially according to:

* TODO.md,
* EXECUTION.md,
* governance documents,
* architecture plans,
* recovery priorities.

Final output contract:

Do not claim completion unless you provide evidence.

If you created only folders without behavior or tests:

* mark the task as SCAFFOLD_ONLY,
* NOT DONE.

You must report:

1. Files changed

* exact paths
* short reason for each file

2. Empty folders check

* command used
* output
* if empty folders remain, explain why

3. One-class-per-file check

* command used
* output
* no multiple production classes in one PHP file unless explicitly justified

4. Forbidden names check

* search for:

    * Services
    * Managers
    * Helpers
    * Utils
    * Support
    * Contracts
    * Descriptions
    * Adapters
* report every remaining match with explanation

5. Old-name check

* search for all renamed concepts
* report stale references

6. Tests

* exact PHPUnit command
* result

7. Static analysis

* exact PHPStan command
* result

8. Autoload

* composer dump-autoload -o result

9. Final status

* DONE only if all checks pass
* PARTIAL if anything remains
* BLOCKED if validation cannot run
