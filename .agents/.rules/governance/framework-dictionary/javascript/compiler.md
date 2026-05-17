# Term
Compiler

# Classification
JavaScript/Node — Transformation Engine

# Purpose
A tool or module that transforms source code from one language, syntax, or representation into another, typically as part of a build or transpilation pipeline.

# Why Allowed
Compilers and compiler-related tooling are central to modern JavaScript development. Babel transforms modern JavaScript into backward-compatible output. TypeScript compiles typed TypeScript into plain JavaScript. SWC and esbuild provide high-performance compilation for JavaScript, TypeScript, and JSX. Template compilers (Handlebars, Pug, EJS) transform markup templates into render functions. AST-based tools (acorn, meriyah, ts-morph) parse and transform code structures. A compiler has a precise responsibility: it accepts source in one format and produces output in another format, preserving semantics while changing representation. It is not a linter, formatter, or runtime executor.

# Allowed Contexts
- JavaScript transpilers (Babel, SWC, TypeScript, esbuild)
- Template compilers (Handlebars, Pug, EJS, JSX transform)
- CSS compilers (PostCSS with plugins, Sass, Less, Stylus)
- AST parsing and transformation tools (acorn, meriyah, recast, ts-morph)
- WebAssembly compilers (AssemblyScript)
- Markdown-to-HTML compilers (unified/remark/rehype pipelines)
- Macro and code generation compilers

# Forbidden Misuse
- Naming a simple string replacement function a "compiler" when it performs no structured transformation
- Creating a Compilers/ folder for general utility functions that happen to process strings
- Calling a template renderer a "compiler" if it does not produce a separate compiled output (rendering is not compiling)
- Using "compiler" to describe a bundler that only concatenates files without transformation

# Ecosystem References
- https://babeljs.io/
- https://www.typescriptlang.org/docs/handbook/compiler-options.html
- https://swc.rs/docs/
- https://esbuild.github.io/
- https://unifiedjs.com/
- https://handlebarsjs.com/

# Allowed Patterns
- babelCompiler
- typescriptCompiler
- handlebarsTemplateCompiler
- sassStyleCompiler
- jsxTransformCompiler
- markdownContentCompiler

# Forbidden Patterns
- Compiler (as a folder name)
- CompilerManager
- DataCompiler (too vague — should specify source and target formats)
- CompilerHelper
- GenericCompiler
