<?php declare(strict_types=1);

namespace GraphQL\Examples\Blog\Type\Scalar;

use const FILTER_VALIDATE_EMAIL;
use function filter_var;
use GraphQL\Error\Error;
use GraphQL\Error\SerializationError;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;

class EmailType extends ScalarType
{
    public function serialize($value): string
    {
        if (! $this->isEmail($value)) {
            throw new SerializationError('Cannot represent value as email: ' . Utils::printSafe($value));
        }

        return $value;
    }

    public function parseValue($value): string
    {
        if (! $this->isEmail($value)) {
            throw new Error('Cannot represent value as email: ' . Utils::printSafe($value));
        }

        return $value;
    }

    public function parseLiteral(Node $valueNode, ?array $variables = null): string
    {
        // Note: throwing GraphQL\Error\Error vs \UnexpectedValueException to benefit from GraphQL
        // error location in query:
        if (! $valueNode instanceof StringValueNode) {
            throw new Error('Query error: Can only parse strings got: ' . $valueNode->kind, [$valueNode]);
        }

        $value = $valueNode->value;
        if (! $this->isEmail($value)) {
            throw new Error('Not a valid email', [$valueNode]);
        }

        return $value;
    }

    /**
     * Is the value a valid email?
     *
     * @param mixed $value
     */
    private function isEmail($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}
