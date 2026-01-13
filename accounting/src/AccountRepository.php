<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting;

interface AccountRepository
{
    public function find(AccountId $accountId): ?Account;

    public function save(Account $account): Account;

    /**
     * @return list<Account>
     */
    public function findAll(): array;
}
