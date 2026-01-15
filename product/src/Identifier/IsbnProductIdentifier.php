<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Identifier;

use InvalidArgumentException;

/**
 * Product identifier using ISBN (International Standard Book Number) format.
 * Supports both ISBN-10 and ISBN-13 formats.
 * Commonly used for books and publications.
 */
final readonly class IsbnProductIdentifier extends ProductIdentifier
{
    private const ISBN_10_LENGTH = 10;
    private const ISBN_13_LENGTH = 13;

    private function __construct(
        private string $value
    ) {
        $this->validate($this->value);
    }

    public static function of(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function asString(): string
    {
        return $this->value;
    }

    public function type(): string
    {
        return 'ISBN';
    }

    private function validate(string $value): void
    {
        // Remove hyphens for validation
        $normalized = str_replace('-', '', $value);
        $length = strlen($normalized);

        if ($length !== self::ISBN_10_LENGTH && $length !== self::ISBN_13_LENGTH) {
            throw new InvalidArgumentException(
                'ISBN must be 10 or 13 characters (excluding hyphens)'
            );
        }

        // ISBN-10 can have digits and 'X' (check digit)
        // ISBN-13 must be all digits
        if ($length === self::ISBN_10_LENGTH) {
            // For ISBN-10, allow digits and X as last character
            if (!preg_match('/^\d{9}[\dX]$/', $normalized)) {
                throw new InvalidArgumentException(
                    'ISBN contains invalid characters'
                );
            }
        } else {
            if (!ctype_digit($normalized)) {
                throw new InvalidArgumentException(
                    'ISBN contains invalid characters'
                );
            }
        }
    }
}
