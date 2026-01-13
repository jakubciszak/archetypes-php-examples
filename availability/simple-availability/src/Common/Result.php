<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Common;

use Closure;

/**
 * @template-covariant F
 * @template-covariant S
 */
final readonly class Result
{
    /**
     * @param S|null $success
     * @param F|null $failure
     */
    private function __construct(
        private mixed $success,
        private mixed $failure,
        private bool $isSuccess
    ) {
    }

    /**
     * @template T
     * @param T $value
     * @return self<null, T>
     * @phpstan-return self<never, T>
     */
    public static function success(mixed $value): self
    {
        /** @phpstan-ignore-next-line */
        return new self($value, null, true);
    }

    /**
     * @template T
     * @param T $value
     * @return self<T, null>
     * @phpstan-return self<T, never>
     */
    public static function failure(mixed $value): self
    {
        /** @phpstan-ignore-next-line */
        return new self(null, $value, false);
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function isFailure(): bool
    {
        return !$this->isSuccess;
    }

    /**
     * @return S
     */
    public function getSuccess(): mixed
    {
        if (!$this->isSuccess) {
            throw new \LogicException('Cannot get success value from a failure result');
        }
        assert($this->success !== null);
        return $this->success;
    }

    /**
     * @return F
     */
    public function getFailure(): mixed
    {
        if ($this->isSuccess) {
            throw new \LogicException('Cannot get failure value from a success result');
        }
        assert($this->failure !== null);
        return $this->failure;
    }

    /**
     * @template T
     * @param Closure(S): T $mapper
     * @return self<F, T>
     */
    public function map(Closure $mapper): self
    {
        if ($this->isSuccess) {
            assert($this->success !== null);
            return self::success($mapper($this->success));
        }
        /** @var self<F, T> */
        return self::failure($this->failure);
    }

    /**
     * @template T
     * @param Closure(F): T $mapper
     * @return self<T, S>
     */
    public function mapFailure(Closure $mapper): self
    {
        if ($this->isFailure()) {
            assert($this->failure !== null);
            return self::failure($mapper($this->failure));
        }
        /** @var self<T, S> */
        return self::success($this->success);
    }

    /**
     * @template SF
     * @template SS
     * @param Closure(S): self<SF, SS> $mapper
     * @return self<F|SF, SS>
     */
    public function flatMap(Closure $mapper): self
    {
        if ($this->isSuccess) {
            /** @phpstan-ignore-next-line */
            return $mapper($this->success);
        }
        /** @var self<F|SF, SS> */
        return self::failure($this->failure);
    }

    /**
     * @template T
     * @param Closure(S): T $successMapper
     * @param Closure(F): T $failureMapper
     * @return T
     */
    public function fold(Closure $successMapper, Closure $failureMapper): mixed
    {
        if ($this->isSuccess) {
            assert($this->success !== null);
            return $successMapper($this->success);
        }
        assert($this->failure !== null);
        return $failureMapper($this->failure);
    }

    /**
     * @param Closure(S): void $action
     * @return self<F, S>
     */
    public function peek(Closure $action): self
    {
        if ($this->isSuccess) {
            assert($this->success !== null);
            $action($this->success);
        }
        return $this;
    }

    /**
     * @param Closure(S): void $action
     * @return self<F, S>
     */
    public function peekSuccess(Closure $action): self
    {
        return $this->peek($action);
    }

    /**
     * @param Closure(F): void $action
     * @return self<F, S>
     */
    public function peekFailure(Closure $action): self
    {
        if ($this->isFailure()) {
            assert($this->failure !== null);
            $action($this->failure);
        }
        return $this;
    }

    /**
     * @param Closure(S): void $successAction
     * @param Closure(F): void $failureAction
     * @return self<F, S>
     */
    public function peekBoth(Closure $successAction, Closure $failureAction): self
    {
        if ($this->isSuccess) {
            assert($this->success !== null);
            $successAction($this->success);
        } else {
            assert($this->failure !== null);
            $failureAction($this->failure);
        }
        return $this;
    }

    /**
     * @template T
     * @param Closure(S): T $successMapper
     * @param Closure(F): self<F, T> $failureMapper
     * @return self<F, T>
     */
    /** @phpstan-ignore-next-line generics.variance */
    public function ifSuccessOrElse(Closure $successMapper, Closure $failureMapper): self
    {
        if ($this->isSuccess) {
            assert($this->success !== null);
            return self::success($successMapper($this->success));
        }
        assert($this->failure !== null);
        /** @phpstan-ignore-next-line */
        return $failureMapper($this->failure);
    }

    /**
     * @template FS
     * @template FF
     * @param Closure(S): FS $successMapper
     * @param Closure(F): FF $failureMapper
     * @return self<FF, FS>
     */
    public function biMap(Closure $successMapper, Closure $failureMapper): self
    {
        if ($this->isSuccess) {
            assert($this->success !== null);
            return self::success($successMapper($this->success));
        }
        assert($this->failure !== null);
        return self::failure($failureMapper($this->failure));
    }
}
