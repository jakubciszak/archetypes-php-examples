<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Constraint;

use InvalidArgumentException;
use SoftwareArchetypes\Product\FeatureValueType;

/**
 * Constraint that validates text values against a regular expression pattern.
 * Useful for enforcing format requirements like codes, identifiers, or structured text.
 */
final readonly class RegexConstraint implements FeatureValueConstraint
{
    public function __construct(
        private string $pattern
    ) {
        if ($this->pattern === '') {
            throw new InvalidArgumentException('Pattern cannot be empty');
        }

        // Validate that the pattern is a valid regex
        if (@preg_match($this->pattern, '') === false) {
            throw new InvalidArgumentException('Invalid regular expression pattern');
        }
    }

    public function isSatisfiedBy(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return preg_match($this->pattern, $value) === 1;
    }

    public function valueType(): FeatureValueType
    {
        return FeatureValueType::TEXT;
    }

    public function pattern(): string
    {
        return $this->pattern;
    }
}
