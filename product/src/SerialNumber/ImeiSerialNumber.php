<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\SerialNumber;

use InvalidArgumentException;

/**
 * IMEI (International Mobile Equipment Identity) serial number.
 * Used for mobile devices and must be exactly 15 digits.
 */
final readonly class ImeiSerialNumber extends SerialNumber
{
    private const IMEI_LENGTH = 15;

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
        return 'IMEI';
    }

    private function validate(string $value): void
    {
        if (strlen($value) !== self::IMEI_LENGTH) {
            throw new InvalidArgumentException(
                'IMEI must be exactly 15 digits'
            );
        }

        if (!ctype_digit($value)) {
            throw new InvalidArgumentException(
                'IMEI must contain only digits'
            );
        }
    }
}
