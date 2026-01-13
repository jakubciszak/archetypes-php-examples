<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting;

use SoftwareArchetypes\Accounting\Events\AccountingEvent;
use SoftwareArchetypes\Accounting\Events\CreditEntryRegistered;
use SoftwareArchetypes\Accounting\Events\DebitEntryRegistered;

final class Account
{
    private Entries $newEntries;

    /**
     * @var list<AccountingEvent>
     */
    private array $pendingEvents = [];

    private function __construct(
        private readonly AccountId $accountId,
        private readonly AccountType $type,
        private readonly string $name,
        private Money $balance,
    ) {
        $this->newEntries = Entries::empty();
    }

    public static function create(
        AccountId $accountId,
        AccountType $type,
        string $name,
        ?Money $initialBalance = null,
    ): self {
        return new self(
            $accountId,
            $type,
            $name,
            $initialBalance ?? Money::zero(),
        );
    }

    public function id(): AccountId
    {
        return $this->accountId;
    }

    public function type(): AccountType
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function balance(): Money
    {
        return $this->balance;
    }

    public function addEntry(Entry $entry): void
    {
        $this->newEntries->add($entry);
        $this->balance = $this->balance->add($entry->amount());
        $this->recordEntryEvent($entry);
    }

    /**
     * @param list<Entry> $entries
     */
    public function addEntries(array $entries): void
    {
        foreach ($entries as $entry) {
            $this->addEntry($entry);
        }
    }

    /**
     * @return list<Entry>
     */
    public function entries(): array
    {
        return $this->newEntries->toList();
    }

    /**
     * @return list<AccountingEvent>
     */
    public function pendingEvents(): array
    {
        return $this->pendingEvents;
    }

    public function clearPendingEvents(): void
    {
        $this->pendingEvents = [];
    }

    private function recordEntryEvent(Entry $entry): void
    {
        $event = match (true) {
            $entry instanceof AccountDebited => new DebitEntryRegistered(
                $entry->id(),
                $entry->transactionId(),
                $entry->accountId(),
                $entry->amount()->negate(), // Convert back to positive for event
                $entry->occurredAt(),
            ),
            $entry instanceof AccountCredited => new CreditEntryRegistered(
                $entry->id(),
                $entry->transactionId(),
                $entry->accountId(),
                $entry->amount(),
                $entry->occurredAt(),
            ),
            default => throw new \LogicException('Unknown entry type'),
        };

        $this->pendingEvents[] = $event;
    }
}
