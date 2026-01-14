<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Exceptions;

/**
 * Thrown when a transfer operation violates business rules.
 */
final class InvalidTransferException extends DomainException
{
    public static function amountMustBePositive(): self
    {
        return new self('Transfer amount must be positive');
    }

    public static function cannotTransferToSameAccount(): self
    {
        return new self('Cannot transfer to the same account');
    }
}
