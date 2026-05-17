# Term
Loader

# Classification
JavaScript/Node — Import and Transformation Pattern

# Purpose
A module that intercepts file imports or resource requests, reads the raw content, transforms it if necessary, and returns it in a format the runtime or build tool can consume.

# Why Allowed
Loaders are a core concept in the JavaScript build and runtime ecosystem. webpack loaders transform files at import time (babel-loader, css-loader, file-loader). Node.js supports ES module loaders via the --experimental-loader flag for custom import resolution and transformation. Build tools like Vite, Rollup, and esbuild have loader concepts for handling non-JavaScript assets (images, fonts, CSS, markdown, YAML). A loader's responsibility is narrow and well-defined: it sits at the import boundary, reads a resource, optionally transforms it, and returns it in a consumable format. It is not a general file reader or a data fetcher.

# Allowed Contexts
- webpack loaders (babel-loader, css-loader, ts-loader, sass-loader)
- Node.js ES module loaders (--experimental-loaders, register() API)
- Build tool resource loaders (Vite, Rollup, esbuild asset handling)
- Static site generator content loaders (Markdown, MDX, YAML frontmatter)
- Font and image loaders in asset pipelines
- WebAssembly module loaders

# Forbidden Misuse
- Naming a database query function a "loader" when it does not intercept imports or load resources
- Creating a Loaders/ folder for general data-fetching utilities
- Calling a UI data prefetch hook a "loader" when it performs no file or module transformation
- Using "loader" to describe any function that reads data from any source

# Ecosystem References
- https://webpack.js.org/concepts/loaders/
- https://nodejs.org/api/module.html#customization-hooks-api
- https://vitejs.dev/guide/features.html#static-assets
- https://esbuild.github.io/content-types/
- https://mdxjs.com/

# Allowed Patterns
- babelLoader
- cssModuleLoader
- markdownLoader
- wasmLoader
- imageAssetLoader
- yamlConfigLoader

# Forbidden Patterns
- Loader (as a folder name)
- DataLoader (too broad — should specify what is being loaded)
- LoaderManager
- LoaderHelper
- UniversalLoader
