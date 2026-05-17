# How This Works

`Query/IR/Nodes` contains the explicit parts of a query plan.

- Structural nodes: `QueryNode`, `SelectNode`, `FromNode`, `JoinNode`, `GroupByNode`, `OrderByNode`, `HavingNode`,
  `ProjectionNode`.
- Predicate/value helpers: `WhereNode`, `ComparisonOperator`.
- CTE helpers: `CTENode`, `CTEType`.

Each file owns one responsibility so Composer autoload, reviewability, and future growth remain predictable.

