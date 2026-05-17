# Term
Plugin

# Classification
JavaScript/Node — Extension Mechanism

# Purpose
A self-contained module that registers itself with a host system to extend, modify, or hook into its behavior without altering the host's core code.

# Why Allowed
The plugin pattern is deeply established across the JavaScript ecosystem. Build tools (webpack, Vite, Rollup, esbuild), linters (ESLint, Stylelint), test frameworks (Jest, Vitest), package managers (pnpm, npm), editors (VS Code extensions), and browsers (WebExtensions API) all use plugins as their primary extension mechanism. A plugin has a clear contract: the host defines a lifecycle or hook interface, and the plugin implements one or more hooks to contribute behavior. Plugins are not arbitrary modules — they must conform to a host-defined registration API and lifecycle.

# Allowed Contexts
- Build tool plugins (webpack, Vite, Rollup, esbuild, Babel, PostCSS)
- Linter and formatter plugins (ESLint, Stylelint, Prettier)
- Test framework plugins (Jest, Vitest, Playwright)
- Browser extensions and WebExtensions
- Editor/IDE extensions (VS Code, Monaco)
- Package manager plugins and hooks
- Framework plugin systems (AdonisJS, Fastify, Strapi)

# Forbidden Misuse
- Naming any loosely-coupled module a "plugin" when it never registers with a host lifecycle
- Creating a Plugin/ folder as a catch-all for independent utilities
- Calling a configuration object a "plugin" when it has no hook or lifecycle behavior
- Using "plugin" to describe a dependency that is simply imported and called directly

# Ecosystem References
- https://vitejs.dev/guide/api-plugin.html
- https://webpack.js.org/contribute/writing-a-plugin/
- https://eslint.org/docs/latest/extend/plugins
- https://www.fastify.io/docs/latest/Reference/Plugins/
- https://rollupjs.org/plugin-development/

# Allowed Patterns
- vitePluginSsr
- eslintPluginSecurity
- webpackPluginMinify
- babelPluginTransformOptionalChaining
- jestPluginSnapshotDiff
- fastifyPluginCors

# Forbidden Patterns
- Plugin (as a folder name)
- PluginManager (unless it genuinely manages plugin registration lifecycle)
- PluginHelper
- GenericPlugin
- PluginUtility
