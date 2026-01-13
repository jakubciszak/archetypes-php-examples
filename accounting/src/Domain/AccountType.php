<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Domain;

enum AccountType
{
    case ASSET;
    case OFF_BALANCE;
    case EXPENSE;
    case LIABILITY;
    case REVENUE;

    public function isDoubleEntryBookingEnabled(): bool
    {
        return match ($this) {
            self::ASSET => true,
            self::EXPENSE => true,
            self::LIABILITY => true,
            self::REVENUE => true,
            self::OFF_BALANCE => false,
        };
    }
}
