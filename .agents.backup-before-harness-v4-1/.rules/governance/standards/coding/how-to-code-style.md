Fix:

Unhandled exceptions
constructor promotion is must have !!!
Fully qualified name usage - replace qualifier with an import.
Named Arguments - must have
@throw everywhere
If constructor has only one depebdency,make it one line like this:

    public function __construct(private readonly QueryBuilder $queryBuilder) {}

Null object is a must:
public function findById(int $id) : object|null
I don't want to see ? anywhere, use "string|null" instead of "?string" and so on!!!
nullable type must be written as "string|null".
Make parameter type nullable: parameter type is implicitly null.

Make pipe whenever is possible: Chain of assignments can be replaced with a pipe operator
Make function anonymous function static: 🔨 PHP Hammer: anonymous function can be static. 