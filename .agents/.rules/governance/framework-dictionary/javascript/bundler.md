# Term
Bundler

# Classification
JavaScript/Node — Build Tool

# Purpose
A tool that analyzes module dependency graphs, combines multiple source files into optimized output bundles, and applies transformations such as tree-shaking, code splitting, and minification.

# Why Allowed
Module bundling is a defining concern in the JavaScript ecosystem due to the module-based nature of modern web applications. webpack pioneered comprehensive bundling with a rich plugin ecosystem. Vite uses Rollup for production bundling while leveraging native ES modules in development. esbuild and SWC provide high-performance bundling alternatives. Parcel offers zero-configuration bundling. A bundler has a precise responsibility: it starts from entry points, traverses import/require dependencies, resolves modules, transforms them through loaders or plugins, and emits optimized output bundles. It is distinct from a compiler (which transforms single files) and a task runner (which orchestrates arbitrary shell commands).

# Allowed Contexts
- Application bundlers (webpack, Vite/Rollup, esbuild, Parcel, Browserify)
- Library bundlers (Rollup, tsup, microbundle, unbuild)
- Server-side bundling (SSR bundle builds, edge function bundlers)
- Code splitting and chunk configuration
- Tree-shaking and dead code elimination
- Asset bundling (CSS, images, fonts alongside JavaScript)
- Monorepo workspace bundling

# Forbidden Misuse
- Naming a simple file concatenation script a "bundler" when it performs no dependency analysis or transformation
- Creating a Bundlers/ folder for general build scripts that do not produce module bundles
- Calling a minifier a "bundler" when it only compresses output without analyzing dependencies
- Using "bundler" to describe a deployment packaging tool that zips files

# Ecosystem References
- https://webpack.js.org/
- https://vitejs.dev/guide/build.html
- https://rollupjs.org/
- https://esbuild.github.io/
- https://parceljs.org/
- https://github.com/egoist/tsup

# Allowed Patterns
- appBundler
- libraryBundler
- serverBundleConfig
- vendorChunkBundler
- ssrEntryBundler
- edgeFunctionBundler

# Forbidden Patterns
- Bundler (as a folder name)
- BundlerManager
- BuildBundler (redundant and vague)
- BundlerHelper
- GenericBundler
