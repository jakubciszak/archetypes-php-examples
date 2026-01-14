<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing;

use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use InvalidArgumentException;

final class Parameters
{
    /**
     * @var array<string, mixed>
     */
    private array $values;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public static function empty(): self
    {
        return new self();
    }

    public function getBigDecimal(string $key): BigDecimal
    {
        $value = $this->values[$key] ?? null;

        if ($value instanceof BigDecimal) {
            return $value;
        }

        if (is_numeric($value)) {
            try {
                return BigDecimal::of((string) $value);
            } catch (MathException $e) {
                throw new InvalidArgumentException(sprintf('Cannot convert %s to BigDecimal', $value), 0, $e);
            }
        }

        throw new InvalidArgumentException(sprintf('Cannot convert %s to BigDecimal', get_debug_type($value)));
    }

    public function contains(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * @param array<string> $keys
     */
    public function containsAll(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->contains($key)) {
                return false;
            }
        }

        return true;
    }

    public function get(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    /**
     * @return array<string>
     */
    public function keys(): array
    {
        return array_keys($this->values);
    }

    /**
     * @return array<string, mixed>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function setValues(array $values): void
    {
        $this->values = $values;
    }
}
