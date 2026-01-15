<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\SerialNumber;

use InvalidArgumentException;

/**
 * VIN (Vehicle Identification Number) serial number.
 * Used for vehicles and must be exactly 17 characters.
 * VIN uses capital letters and digits, excluding I, O, and Q to avoid confusion.
 */
final readonly class VinSerialNumber extends SerialNumber
{
    private const VIN_LENGTH = 17;

    private string $value;

    private function __construct(string $value)
    {
        // Normalize to uppercase
        $normalized = strtoupper($value);
        $this->validate($normalized);
        $this->value = $normalized;
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
        return 'VIN';
    }

    private function validate(string $value): void
    {
        if (strlen($value) !== self::VIN_LENGTH) {
            throw new InvalidArgumentException(
                'VIN must be exactly 17 characters'
            );
        }

        // VIN uses A-Z (excluding I, O, Q) and 0-9
        if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $value)) {
            throw new InvalidArgumentException(
                'VIN contains invalid characters (must be A-Z excluding I, O, Q and 0-9)'
            );
        }
    }
}
