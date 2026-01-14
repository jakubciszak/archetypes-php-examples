<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Exceptions;

/**
 * Thrown when attempting to access an account that does not exist.
 */
final class AccountNotFoundException extends DomainException
{
    public static function forId(string $accountId): self
    {
        return new self("Account with ID '{$accountId}' not found");
    }
}
