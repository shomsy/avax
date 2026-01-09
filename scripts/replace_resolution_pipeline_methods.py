import re
from pathlib import Path


path = Path("Foundation/Container/Act/Resolve/ResolutionPipeline.php")
text = path.read_text(encoding="utf-8")


def replace_docblock(func_name: str, new_block: str, visibility: str = "public") -> None:
    global text
    pattern = re.compile(
        r"/\*\*.*?\*/\s+"
        + re.escape(visibility)
        + r"\s+function\s+"
        + re.escape(func_name)
        + r"\s*\(",
        re.S,
    )
    replacement = new_block + "\n    " + visibility + " function " + func_name + "("
    text, count = pattern.subn(replacement, text, count=1)
    if count == 0:
        raise SystemExit(f"Docblock for {func_name} not found.")


replace_docblock(
    "__construct",
    """    /**
     * Initialize the default resolution pipeline.
     *
     * @see docs/classes/Resolve/ResolutionPipeline.html#__construct
     */""",
)

replace_docblock(
    "send",
    """    /**
     * Set the resolution context to be processed.
     *
     * @see docs/classes/Resolve/ResolutionPipeline.html#send
     */""",
)

replace_docblock(
    "through",
    """    /**
     * Configure the pipe execution order.
     *
     * @see docs/classes/Resolve/ResolutionPipeline.html#through
     */""",
)

replace_docblock(
    "thenReturn",
    """    /**
     * Execute the configured pipeline and return the result.
     *
     * @see docs/classes/Resolve/ResolutionPipeline.html#thenReturn
     */""",
)

replace_docblock(
    "carry",
    """    /**
     * Build the pipeline execution chain.
     *
     * @see docs/classes/Resolve/ResolutionPipeline.html#carry
     */""",
    visibility="private",
)

replace_docblock(
    "getPipeInstance",
    """    /**
     * Resolve a pipe class name to its instance.
     *
     * @see docs/classes/Resolve/ResolutionPipeline.html#getPipeInstance
     */""",
    visibility="private",
)

path.write_text(text, encoding="utf-8")
