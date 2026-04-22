READ from AI Prompts folder. All how-to-* files are the main source of truth for coding standards.
MAKE backup after any changes by executing the backup script ./merge-files.sh . from the root of the component (
Foundation/HTTP/Request).
USE backup file (made by merge-file.sh) to restore the component if you refactor, rewrite, or whenever you need to
revert changes.