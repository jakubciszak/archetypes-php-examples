<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Infrastructure;

use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccount;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccountId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccountRepository;

final class InMemoryLoyaltyAccountRepository implements LoyaltyAccountRepository
{
    /**
     * @var array<string, LoyaltyAccount>
     */
    private array $accounts = [];

    public function save(LoyaltyAccount $account): LoyaltyAccount
    {
        $this->accounts[$account->id()->toString()] = $account;
        return $account;
    }

    public function find(LoyaltyAccountId $accountId): ?LoyaltyAccount
    {
        return $this->accounts[$accountId->toString()] ?? null;
    }

    public function findByCustomerId(string $customerId): ?LoyaltyAccount
    {
        foreach ($this->accounts as $account) {
            if ($account->customerId() === $customerId) {
                return $account;
            }
        }
        return null;
    }

    /**
     * @return list<LoyaltyAccount>
     */
    public function findAll(): array
    {
        return array_values($this->accounts);
    }
}
