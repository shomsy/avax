# CSS Governance Profile

Use this profile for CSS-heavy repositories, design systems, and frontend code
bases where styles are part of the shipped product contract.

This profile assumes modern CSS with layering, tokens, and reusable component
contracts.

Apply it together with the relevant architecture overlay under
`../../architecture/profiles/**` when repo shape and slice ownership matter.

## Architectural Layer Rules

- define a deliberate style architecture such as settings, foundation, layout,
  components, and utilities; do not let styles accrete in one undifferentiated
  file
- keep token/configuration files separate from selector-bearing files
- keep layout primitives separate from product semantics and behavior hooks
- use aggregation entrypoints for major style layers so import order is owned
  and reviewable
- prefer derived build artifacts from canonical source styles; do not hand-edit
  generated CSS

## Token And Theme Rules

- design tokens should be first-class contracts, preferably through CSS custom
  properties
- token files should define values, not selectors or component styling
- theme aggregation order must be explicit because later imports can override
  earlier variables intentionally or accidentally
- spacing, radius, elevation, container, safe-area, and color values should
  come from a documented scale rather than scattered magic numbers
- if runtime theming matters, prefer custom properties over preprocessor-only
  variables that disappear after build

## Cascade, Layer, And Specificity Rules

- use `@layer` or an equivalent discipline to make precedence explicit
- keep selector specificity low and predictable; prefer classes and controlled
  host states over ID selectors or deep descendant chains
- use `:where()` or equivalent low-specificity strategies when building
  reusable APIs that must remain easy to override
- `!important` is an escape hatch and must be rare, localized, and justified
- import and layer order are part of the architecture; changing them is a
  behavioral change, not formatting noise

## Layout And Composition Rules

- layout primitives should own geometry and flow, not component business meaning
- do not move layout math into inline styles, `data-*` attributes, or
  JavaScript mutation when plain CSS can own the behavior
- use container-scoped responsiveness when the component contract is local;
  avoid viewport-only thinking for reusable slices
- z-index usage should follow a named scale or token contract to prevent
  layering drift
- responsive collapse, span safety, and overflow handling should be encoded as
  explicit rules, not left to accidental browser behavior

## Component And State Authoring Rules

- component styles should expose clear class or host-state contracts
- keep state styling explicit through classes, attributes, or host states rather
  than selector guesswork
- prefer CSS variables for component theming and host overrides
- for Shadow DOM components, use `:host`, explicit host states, and controlled
  `::slotted(...)` contracts instead of leaking assumptions across the boundary
- no inline styles in canonical markup for product UI unless the inline value is
  the explicit public API of the component

## Runtime And Modern CSS Rules

- use modern CSS features deliberately: container queries, subgrid,
  `content-visibility`, and similar features should improve architecture, not
  become novelty debt
- when a modern feature changes fallback behavior, document and test the
  non-support path or browser floor
- transitions and motion should be meaningful, accessible, and cheap enough for
  the interaction path
- avoid CSS that requires script to constantly patch geometry if a layout or
  token solution can own it
- if styles are distributed through constructed stylesheets or adopted
  stylesheets, the ownership and update path must stay explicit

## Naming And Namespace Rules

- keep naming families intentional: layout names for geometry, component names
  for product semantics, utility names for narrow overrides, and behavior hooks
  for script ownership
- do not mix architectural roles inside one naming family
- prefer names that describe the stable contract of the class, not temporary
  visual appearance or a current page context
- helper and debug classes must stay obviously non-product and easy to remove
- when a design system locks primitive namespaces, new product work should
  compose them rather than invent incompatible parallel primitives

## Security And Safety Rules

- do not inject untrusted CSS, HTML, or style attributes without a deliberate
  sanitization and policy boundary
- keep CSP compatibility in mind; avoid style patterns that depend on unsafe
  inline execution when a stylesheet contract can be used instead
- never let user-controlled values write arbitrary URLs, selectors, or CSS text
  into shipped style surfaces
- style hooks must not become covert data channels for secrets or privileged
  state
- debug or diagnostic styles must not leak into production bundles silently

## Accessibility And UX Rules

- do not remove focus indicators without replacing them with an accessible
  alternative
- hidden and visually-hidden patterns must preserve or remove accessibility
  semantics intentionally, not accidentally
- color contrast, reduced-motion behavior, and readable spacing are part of the
  style contract, not post-release polish
- state changes visible only by color require an additional perceivable cue
- CSS that breaks keyboard reachability, text scaling, or content order is a
  product defect, not a styling quirk

## Testing And Delivery Rules

- lint CSS with a real ruleset; formatting alone is not quality control
- add visual regression or screenshot coverage where layout and theme contracts
  are critical
- guard token integrity, layer order, and generated-artifact consistency in CI
  when styles are part of the reusable platform
- review changes to reset, theme, layout, and utility layers as architecture
  changes because they affect broad blast radius
- keep documentation close enough to the code that consumers can understand how
  tokens, layers, and primitives are intended to compose
