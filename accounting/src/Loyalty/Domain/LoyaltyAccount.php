<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\PostingRule;

/**
 * LoyaltyAccount is the aggregate root for loyalty program management.
 *
 * Based on the Accounting archetype pattern from "Software Archetypes" (Chapter 7).
 *
 * This implements a full entry-based ledger system with:
 * - Hierarchical sub-accounts
 * - Immutable entries
 * - Balance calculation from entries
 * - Transaction processing via PostingRules
 *
 * Account Hierarchy:
 * LoyaltyAccount (root)
 * ├── PendingFromPurchases
 * ├── PendingFromPromos
 * ├── ActivePoints
 * ├── SpentPoints
 * ├── ExpiredPoints
 * ├── ReversedPoints
 * └── AdjustmentPoints
 */
final class LoyaltyAccount
{
    /**
     * @var array<string, Account>
     */
    private array $accounts = [];

    /**
     * @var list<PostingRule>
     */
    private array $postingRules = [];

    /**
     * @var list<Transaction>
     */
    private array $pendingTransactions = [];

    private function __construct(
        private readonly LoyaltyAccountId $accountId,
        private readonly string $customerId,
        private readonly string $customerName,
        private readonly AccountingPractice $accountingPractice,
    ) {
        $this->initializeAccounts();
    }

    public static function create(
        LoyaltyAccountId $accountId,
        string $customerId,
        string $customerName,
        AccountingPractice $accountingPractice,
    ): self {
        return new self($accountId, $customerId, $customerName, $accountingPractice);
    }

    private function initializeAccounts(): void
    {
        // Initialize all sub-accounts
        foreach (AccountType::cases() as $type) {
            $this->accounts[$type->value] = Account::create($type);
        }
    }

    public function id(): LoyaltyAccountId
    {
        return $this->accountId;
    }

    public function customerId(): string
    {
        return $this->customerId;
    }

    public function customerName(): string
    {
        return $this->customerName;
    }

    public function accountingPractice(): AccountingPractice
    {
        return $this->accountingPractice;
    }

    /**
     * Register a posting rule for processing transactions.
     */
    public function registerPostingRule(PostingRule $rule): void
    {
        $this->postingRules[] = $rule;
    }

    /**
     * Process a transaction using registered posting rules.
     *
     * This is the KEY method that implements the Accounting archetype pattern:
     * Transaction → PostingRule → Entries → Updated Balances
     */
    public function processTransaction(Transaction $transaction): void
    {
        foreach ($this->postingRules as $rule) {
            if ($rule->canProcess($transaction)) {
                $rule->process($transaction, $this);
                $this->pendingTransactions[] = $transaction;
                return;
            }
        }

        throw new \RuntimeException(
            sprintf(
                'No posting rule found for transaction type: %s',
                $transaction->type()
            )
        );
    }

    /**
     * Add an entry to a specific account.
     *
     * This is called by PostingRules.
     */
    public function addEntry(Entry $entry): void
    {
        $accountType = $entry->accountType();

        if (!isset($this->accounts[$accountType->value])) {
            throw new \InvalidArgumentException(
                sprintf('Account type %s does not exist', $accountType->value)
            );
        }

        $this->accounts[$accountType->value]->addEntry($entry);
    }

    /**
     * Get a specific sub-account.
     */
    public function account(AccountType $type): Account
    {
        if (!isset($this->accounts[$type->value])) {
            throw new \InvalidArgumentException(
                sprintf('Account type %s does not exist', $type->value)
            );
        }

        return $this->accounts[$type->value];
    }

    /**
     * Get balance of a specific account.
     */
    public function balance(AccountType $type): Points
    {
        return $this->account($type)->balance();
    }

    /**
     * Get balance of active points (ready to use).
     */
    public function activePoints(): Points
    {
        return $this->balance(AccountType::ACTIVE_POINTS);
    }

    /**
     * Get total pending points (from purchases and promos).
     */
    public function totalPendingPoints(): Points
    {
        $fromPurchases = $this->balance(AccountType::PENDING_FROM_PURCHASES);
        $fromPromos = $this->balance(AccountType::PENDING_FROM_PROMOS);

        return $fromPurchases->add($fromPromos);
    }

    /**
     * Get balance of spent points.
     */
    public function spentPoints(): Points
    {
        return $this->balance(AccountType::SPENT_POINTS);
    }

    /**
     * Get balance of expired points.
     */
    public function expiredPoints(): Points
    {
        return $this->balance(AccountType::EXPIRED_POINTS);
    }

    /**
     * Get balance of reversed points (from returns).
     */
    public function reversedPoints(): Points
    {
        return $this->balance(AccountType::REVERSED_POINTS);
    }

    /**
     * Get all entries across all accounts.
     *
     * @return list<Entry>
     */
    public function allEntries(): array
    {
        $entries = [];
        foreach ($this->accounts as $account) {
            $entries = array_merge($entries, $account->entries());
        }
        return $entries;
    }

    /**
     * Get all transactions processed by this account.
     *
     * @return list<Transaction>
     */
    public function transactions(): array
    {
        return $this->pendingTransactions;
    }

    /**
     * Clear pending transactions (after publishing events).
     */
    public function clearPendingTransactions(): void
    {
        $this->pendingTransactions = [];
    }

    /**
     * Get entries for a specific reference (e.g., purchase_id).
     *
     * @return list<Entry>
     */
    public function entriesForReference(string $referenceId): array
    {
        $entries = [];
        foreach ($this->accounts as $account) {
            $entries = array_merge($entries, $account->entriesForReference($referenceId));
        }
        return $entries;
    }

    /**
     * Get entries for a specific line item.
     *
     * @return list<Entry>
     */
    public function entriesForLineItem(string $lineItemId): array
    {
        $entries = [];
        foreach ($this->accounts as $account) {
            $entries = array_merge($entries, $account->entriesForLineItem($lineItemId));
        }
        return $entries;
    }

    /**
     * Calculate points balance for a specific reference across all accounts.
     */
    public function balanceForReference(string $referenceId, AccountType $accountType): Points
    {
        return $this->account($accountType)->balanceForReference($referenceId);
    }

    /**
     * Calculate points balance for a specific line item.
     */
    public function balanceForLineItem(string $lineItemId, AccountType $accountType): Points
    {
        return $this->account($accountType)->balanceForLineItem($lineItemId);
    }
}
