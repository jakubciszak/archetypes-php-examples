<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccount;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccountId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccountRepository;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PromotionalAction;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PurchaseId;
use SoftwareArchetypes\Accounting\Loyalty\Events\EventsPublisher;
use SoftwareArchetypes\Accounting\Money;

/**
 * LoyaltyProgramFacade is the main application service for loyalty program operations.
 *
 * It orchestrates domain logic and publishes events.
 */
final readonly class LoyaltyProgramFacade
{
    public function __construct(
        private LoyaltyAccountRepository $accountRepository,
        private EventsPublisher $eventsPublisher,
    ) {
    }

    /**
     * Create a new loyalty account for a customer.
     */
    public function createAccount(
        LoyaltyAccountId $accountId,
        string $customerId,
        string $customerName,
    ): LoyaltyAccount {
        $account = LoyaltyAccount::create($accountId, $customerId, $customerName);
        return $this->accountRepository->save($account);
    }

    /**
     * Find account by ID.
     */
    public function findAccount(LoyaltyAccountId $accountId): ?LoyaltyAccount
    {
        return $this->accountRepository->find($accountId);
    }

    /**
     * Find account by customer ID.
     */
    public function findAccountByCustomerId(string $customerId): ?LoyaltyAccount
    {
        return $this->accountRepository->findByCustomerId($customerId);
    }

    /**
     * Record a purchase and award points based on posting rule.
     */
    public function recordPurchase(
        LoyaltyAccountId $accountId,
        PurchaseId $purchaseId,
        Money $purchaseAmount,
        PostingRule $postingRule,
        DateTimeImmutable $purchaseDate,
    ): void {
        $account = $this->accountRepository->find($accountId);
        if ($account === null) {
            throw new \InvalidArgumentException(
                sprintf('Loyalty account %s not found', $accountId->toString())
            );
        }

        $account->recordPurchase($purchaseId, $purchaseAmount, $postingRule, $purchaseDate);

        $this->accountRepository->save($account);
        $this->publishEvents($account);
    }

    /**
     * Award promotional bonus points.
     */
    public function awardPromotionalPoints(
        LoyaltyAccountId $accountId,
        PromotionalAction $action,
        DateTimeImmutable $awardDate,
    ): void {
        $account = $this->accountRepository->find($accountId);
        if ($account === null) {
            throw new \InvalidArgumentException(
                sprintf('Loyalty account %s not found', $accountId->toString())
            );
        }

        $account->awardPromotionalPoints($action, $awardDate);

        $this->accountRepository->save($account);
        $this->publishEvents($account);
    }

    /**
     * Activate pending points that have reached their activation date.
     */
    public function activatePendingPoints(
        LoyaltyAccountId $accountId,
        DateTimeImmutable $currentDate,
    ): void {
        $account = $this->accountRepository->find($accountId);
        if ($account === null) {
            throw new \InvalidArgumentException(
                sprintf('Loyalty account %s not found', $accountId->toString())
            );
        }

        $account->activatePendingPoints($currentDate);

        $this->accountRepository->save($account);
        $this->publishEvents($account);
    }

    /**
     * Activate pending points for all accounts (batch process).
     */
    public function activateAllPendingPoints(DateTimeImmutable $currentDate): void
    {
        $accounts = $this->accountRepository->findAll();

        foreach ($accounts as $account) {
            $account->activatePendingPoints($currentDate);
            $this->accountRepository->save($account);
            $this->publishEvents($account);
        }
    }

    /**
     * Reverse points for a returned purchase.
     */
    public function reversePurchase(
        LoyaltyAccountId $accountId,
        PurchaseId $purchaseId,
        DateTimeImmutable $returnDate,
    ): void {
        $account = $this->accountRepository->find($accountId);
        if ($account === null) {
            throw new \InvalidArgumentException(
                sprintf('Loyalty account %s not found', $accountId->toString())
            );
        }

        $account->reversePurchase($purchaseId, $returnDate);

        $this->accountRepository->save($account);
        $this->publishEvents($account);
    }

    /**
     * Use/redeem points.
     */
    public function usePoints(
        LoyaltyAccountId $accountId,
        Points $points,
    ): void {
        $account = $this->accountRepository->find($accountId);
        if ($account === null) {
            throw new \InvalidArgumentException(
                sprintf('Loyalty account %s not found', $accountId->toString())
            );
        }

        $account->usePoints($points);

        $this->accountRepository->save($account);
        $this->publishEvents($account);
    }

    /**
     * Get active points balance.
     */
    public function getActivePoints(LoyaltyAccountId $accountId): ?Points
    {
        $account = $this->accountRepository->find($accountId);
        return $account?->activePoints();
    }

    /**
     * Get total pending points.
     */
    public function getPendingPoints(LoyaltyAccountId $accountId): ?Points
    {
        $account = $this->accountRepository->find($accountId);
        return $account?->totalPendingPoints();
    }

    private function publishEvents(LoyaltyAccount $account): void
    {
        foreach ($account->pendingEvents() as $event) {
            $this->eventsPublisher->publish($event);
        }
        $account->clearPendingEvents();
    }
}
