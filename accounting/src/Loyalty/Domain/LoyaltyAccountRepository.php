<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

interface LoyaltyAccountRepository
{
    public function save(LoyaltyAccount $account): LoyaltyAccount;

    public function find(LoyaltyAccountId $accountId): ?LoyaltyAccount;

    public function findByCustomerId(string $customerId): ?LoyaltyAccount;

    /**
     * @return list<LoyaltyAccount>
     */
    public function findAll(): array;
}
