<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Identifier;

use InvalidArgumentException;

/**
 * Product identifier using GTIN (Global Trade Item Number) format.
 * Supports GTIN-8, GTIN-12, GTIN-13, and GTIN-14 formats.
 * Commonly used for barcodes (UPC, EAN, etc.).
 */
final readonly class GtinProductIdentifier extends ProductIdentifier
{
    private const VALID_LENGTHS = [8, 12, 13, 14];

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
        return 'GTIN';
    }

    private function validate(string $value): void
    {
        $length = strlen($value);

        if (!in_array($length, self::VALID_LENGTHS, true)) {
            throw new InvalidArgumentException(
                'GTIN must be 8, 12, 13, or 14 digits'
            );
        }

        if (!ctype_digit($value)) {
            throw new InvalidArgumentException(
                'GTIN must contain only digits'
            );
        }
    }
}
