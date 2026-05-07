<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel;

use Avax\Components\API\GraphQL\System\Foundation\Failure\GraphQLOperationInvalid;

final class ParseGraphQLOperation
{
    /**
     * @var list<string>
     */
    private array $tokens = [];

    private int $position = 0;

    public function parse(string $source) : ParsedGraphQLOperation
    {
        $this->tokens   = $this->tokenize($source);
        $this->position = 0;

        if ($this->tokens === []) {
            throw new GraphQLOperationInvalid('GraphQL operation must not be empty.');
        }

        $operationType = 'query';
        $operationName = null;

        if ($this->peek() !== '{') {
            $operationType = $this->consumeName('operation type');

            if (! in_array($operationType, ['query', 'mutation'], true)) {
                throw new GraphQLOperationInvalid('GraphQL operation type must be query or mutation.');
            }

            if ($this->isName($this->peek())) {
                $operationName = $this->consumeName('operation name');
            }

            if ($this->peek() === '(') {
                $this->skipGroup('(', ')');
            }
        }

        $this->expect('{');
        $selections = $this->parseSelections();

        if ($this->peek() !== null) {
            throw new GraphQLOperationInvalid('Unexpected token after GraphQL operation.');
        }

        return new ParsedGraphQLOperation(
            type      : $operationType,
            name      : $operationName,
            selections: $selections,
        );
    }

    /**
     * @return list<string>
     */
    private function tokenize(string $source) : array
    {
        preg_match_all(
            '/#[^\r\n]*|"(?:\\\\.|[^"\\\\])*"|[$]?[A-Za-z_][A-Za-z0-9_]*|-?\d+(?:\.\d+)?|[{}\[\]():,!]/',
            $source,
            $matches,
        );

        $tokens = [];

        foreach ($matches[0] as $token) {
            if (str_starts_with($token, '#')) {
                continue;
            }

            $tokens[] = $token;
        }

        return $tokens;
    }

    private function peek() : ?string
    {
        return $this->tokens[$this->position] ?? null;
    }

    private function consumeName(string $label) : string
    {
        $token = $this->consume();

        if (! $this->isName($token)) {
            throw new GraphQLOperationInvalid(sprintf('Expected GraphQL %s.', $label));
        }

        return $token;
    }

    private function consume() : string
    {
        $token = $this->peek();

        if ($token === null) {
            throw new GraphQLOperationInvalid('Unexpected end of GraphQL operation.');
        }

        ++$this->position;

        return $token;
    }

    private function isName(?string $token) : bool
    {
        return $token !== null && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $token) === 1;
    }

    private function skipGroup(string $open, string $close) : void
    {
        $depth = 0;

        do {
            $token = $this->consume();

            if ($token === $open) {
                ++$depth;
            }

            if ($token === $close) {
                --$depth;
            }
        } while ( $depth > 0 && $this->peek() !== null );

        if ($depth !== 0) {
            throw new GraphQLOperationInvalid('GraphQL variable definition is not closed.');
        }
    }

    private function expect(string $token) : void
    {
        $actual = $this->consume();

        if ($actual !== $token) {
            throw new GraphQLOperationInvalid(sprintf('Expected GraphQL token %s.', $token));
        }
    }

    /**
     * @return list<GraphQLSelection>
     */
    private function parseSelections() : array
    {
        $selections = [];

        while ( $this->peek() !== null && $this->peek() !== '}' ) {
            $first = $this->consumeName('field name');
            $alias = null;
            $name  = $first;

            if ($this->accept(':')) {
                $alias = $first;
                $name  = $this->consumeName('field name');
            }

            $arguments = [];

            if ($this->accept('(')) {
                $arguments = $this->parseArguments();
            }

            $children = [];

            if ($this->accept('{')) {
                $children = $this->parseSelections();
            }

            $selections[] = new GraphQLSelection(
                name      : $name,
                alias     : $alias,
                arguments : $arguments,
                selections: $children,
            );
        }

        $this->expect('}');

        return $selections;
    }

    private function accept(string $token) : bool
    {
        if ($this->peek() !== $token) {
            return false;
        }

        ++$this->position;

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseArguments() : array
    {
        $arguments = [];

        while ( $this->peek() !== null && $this->peek() !== ')' ) {
            $name = $this->consumeName('argument name');
            $this->expect(':');
            $arguments[$name] = $this->parseValue();
            $this->accept(',');
        }

        $this->expect(')');

        return $arguments;
    }

    private function parseValue() : mixed
    {
        $token = $this->consume();

        if (str_starts_with($token, '$')) {
            return ['variable' => substr($token, 1)];
        }

        if (str_starts_with($token, '"')) {
            $decoded = json_decode($token, true);

            if (! is_string($decoded)) {
                throw new GraphQLOperationInvalid('GraphQL string argument is invalid.');
            }

            return $decoded;
        }

        if ($token === 'true') {
            return true;
        }

        if ($token === 'false') {
            return false;
        }

        if ($token === 'null') {
            return null;
        }

        if (is_numeric($token)) {
            return str_contains($token, '.') ? (float) $token : (int) $token;
        }

        if ($this->isName($token)) {
            return $token;
        }

        throw new GraphQLOperationInvalid('GraphQL argument value is invalid.');
    }
}
