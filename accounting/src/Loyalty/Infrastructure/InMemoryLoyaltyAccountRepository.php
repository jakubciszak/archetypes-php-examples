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

    /**
     * @var array<string, string> customer_id => account_id
     */
    private array $customerIndex = [];

    public function save(LoyaltyAccount $account): LoyaltyAccount
    {
        $accountId = $account->id()->toString();
        $customerId = $account->customerId();

        $this->accounts[$accountId] = $account;
        $this->customerIndex[$customerId] = $accountId;

        return $account;
    }

    public function find(LoyaltyAccountId $accountId): ?LoyaltyAccount
    {
        return $this->accounts[$accountId->toString()] ?? null;
    }

    public function findByCustomerId(string $customerId): ?LoyaltyAccount
    {
        $accountId = $this->customerIndex[$customerId] ?? null;

        if ($accountId === null) {
            return null;
        }

        return $this->accounts[$accountId] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->accounts);
    }
}
