<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Money;
use SoftwareArchetypes\Accounting\Account;
use SoftwareArchetypes\Accounting\AccountCredited;
use SoftwareArchetypes\Accounting\AccountDebited;
use SoftwareArchetypes\Accounting\AccountId;
use SoftwareArchetypes\Accounting\AccountType;
use SoftwareArchetypes\Accounting\EntryId;
use SoftwareArchetypes\Accounting\TransactionId;
use SoftwareArchetypes\Accounting\AccountRepository;
use SoftwareArchetypes\Accounting\Events\EventsPublisher;

final readonly class AccountingFacade
{
    public function __construct(
        private AccountRepository $accountRepository,
        private EventsPublisher $eventsPublisher,
    ) {
    }

    public function createAccount(
        AccountId $accountId,
        AccountType $type,
        string $name,
        ?Money $initialBalance = null,
    ): Account {
        $account = Account::create($accountId, $type, $name, $initialBalance);
        return $this->accountRepository->save($account);
    }

    public function findAccount(AccountId $accountId): ?Account
    {
        return $this->accountRepository->find($accountId);
    }

    /**
     * @return list<Account>
     */
    public function findAllAccounts(): array
    {
        return $this->accountRepository->findAll();
    }

    public function balance(AccountId $accountId): ?Money
    {
        $account = $this->accountRepository->find($accountId);
        return $account?->balance();
    }

    /**
     * Execute a simple transfer between two accounts
     */
    public function transfer(
        AccountId $fromAccountId,
        AccountId $toAccountId,
        Money $amount,
        DateTimeImmutable $occurredAt,
    ): void {
        $fromAccount = $this->accountRepository->find($fromAccountId);
        $toAccount = $this->accountRepository->find($toAccountId);

        if ($fromAccount === null || $toAccount === null) {
            throw new \RuntimeException('Account not found');
        }

        $transactionId = TransactionId::generate();

        // Debit from source account
        $debitEntry = new AccountDebited(
            EntryId::generate(),
            $transactionId,
            $occurredAt,
            $occurredAt,
            $fromAccountId,
            $amount,
        );
        $fromAccount->addEntry($debitEntry);

        // Credit to destination account
        $creditEntry = new AccountCredited(
            EntryId::generate(),
            $transactionId,
            $occurredAt,
            $occurredAt,
            $toAccountId,
            $amount,
        );
        $toAccount->addEntry($creditEntry);

        // Save accounts and publish events
        $this->accountRepository->save($fromAccount);
        $this->accountRepository->save($toAccount);

        foreach ($fromAccount->pendingEvents() as $event) {
            $this->eventsPublisher->publish($event);
        }
        $fromAccount->clearPendingEvents();

        foreach ($toAccount->pendingEvents() as $event) {
            $this->eventsPublisher->publish($event);
        }
        $toAccount->clearPendingEvents();
    }
}
