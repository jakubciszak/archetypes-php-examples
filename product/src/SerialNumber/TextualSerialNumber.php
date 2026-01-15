<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\SerialNumber;

use InvalidArgumentException;

/**
 * General-purpose textual serial number.
 * Accepts any non-empty string as a serial number.
 */
final readonly class TextualSerialNumber extends SerialNumber
{
    private string $value;

    private function __construct(string $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Serial number cannot be empty');
        }
        $this->value = $trimmed;
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
        return 'TEXTUAL';
    }
}
