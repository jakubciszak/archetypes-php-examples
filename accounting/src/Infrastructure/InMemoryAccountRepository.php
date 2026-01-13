<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Infrastructure;

use SoftwareArchetypes\Accounting\Domain\AccountRepository;
use SoftwareArchetypes\Accounting\Domain\Account;
use SoftwareArchetypes\Accounting\Domain\AccountId;

final class InMemoryAccountRepository implements AccountRepository
{
    /**
     * @var array<string, Account>
     */
    private array $accounts = [];

    public function find(AccountId $accountId): ?Account
    {
        return $this->accounts[$accountId->toString()] ?? null;
    }

    public function save(Account $account): Account
    {
        $this->accounts[$account->id()->toString()] = $account;
        return $account;
    }

    /**
     * @return list<Account>
     */
    public function findAll(): array
    {
        return array_values($this->accounts);
    }
}
