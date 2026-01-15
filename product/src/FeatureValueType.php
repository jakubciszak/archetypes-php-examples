<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product;

use Brick\Math\BigDecimal;
use DateTimeImmutable;

/**
 * Defines the allowed types for product feature values.
 * Restricts feature values to a well-defined set of types.
 */
enum FeatureValueType
{
    case TEXT;
    case INTEGER;
    case DECIMAL;
    case DATE;
    case BOOLEAN;

    /**
     * Checks if the given value is an instance of this type.
     */
    public function isInstance(mixed $value): bool
    {
        return match ($this) {
            self::TEXT => is_string($value),
            self::INTEGER => is_int($value),
            self::DECIMAL => $value instanceof BigDecimal,
            self::DATE => $value instanceof DateTimeImmutable,
            self::BOOLEAN => is_bool($value),
        };
    }

    /**
     * Converts a string representation to the runtime type.
     */
    public function castFromString(string $value): mixed
    {
        return match ($this) {
            self::TEXT => $value,
            self::INTEGER => (int) $value,
            self::DECIMAL => BigDecimal::of($value),
            self::DATE => new DateTimeImmutable($value),
            self::BOOLEAN => in_array(strtolower($value), ['true', '1'], true),
        };
    }

    /**
     * Converts a runtime type to its string representation.
     */
    public function castToString(mixed $value): string
    {
        return match ($this) {
            self::TEXT => (string) $value,
            self::INTEGER => (string) $value,
            self::DECIMAL => (string) $value,
            self::DATE => $value instanceof DateTimeImmutable ? $value->format('Y-m-d') : '',
            self::BOOLEAN => $value ? 'true' : 'false',
        };
    }

    /**
     * Returns the expected PHP type for this feature value type.
     */
    public function phpType(): string
    {
        return match ($this) {
            self::TEXT => 'string',
            self::INTEGER => 'int',
            self::DECIMAL => BigDecimal::class,
            self::DATE => DateTimeImmutable::class,
            self::BOOLEAN => 'bool',
        };
    }
}
