<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Common;

/**
 * @template-covariant F
 * @template-covariant S
 */
final readonly class Result
{
    /**
     * @param F|null $failure
     * @param S|null $success
     */
    private function __construct(
        private mixed $failure,
        private mixed $success
    ) {
    }

    /**
     * @template SF
     * @param SF $value
     * @return self<null, SF>
     */
    public static function success(mixed $value): self
    {
        return new self(null, $value);
    }

    /**
     * @template FF
     * @param FF $value
     * @return self<FF, null>
     */
    public static function failure(mixed $value): self
    {
        return new self($value, null);
    }

    public function isSuccess(): bool
    {
        return $this->success !== null;
    }

    public function isFailure(): bool
    {
        return $this->failure !== null;
    }

    /**
     * @return S
     */
    public function getValue(): mixed
    {
        if ($this->isFailure()) {
            throw new \LogicException('Cannot get value from failure result');
        }
        assert($this->success !== null);
        return $this->success;
    }

    /**
     * @return F
     */
    public function getError(): mixed
    {
        if ($this->isSuccess()) {
            throw new \LogicException('Cannot get error from success result');
        }
        assert($this->failure !== null);
        return $this->failure;
    }
}
