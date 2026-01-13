<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Domain;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Common\Money;

final class Entries
{
    /**
     * @param list<Entry> $entries
     */
    private function __construct(
        private array $entries = [],
    ) {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @param list<Entry> $entries
     */
    public static function of(array $entries): self
    {
        return new self($entries);
    }

    public function add(Entry $entry): self
    {
        $this->entries[] = $entry;
        return $this;
    }

    /**
     * @param list<Entry> $newEntries
     */
    public function addAll(array $newEntries): self
    {
        foreach ($newEntries as $entry) {
            $this->entries[] = $entry;
        }
        return $this;
    }

    public function balanceAsOf(DateTimeImmutable $when): Money
    {
        $balance = Money::zero();

        foreach ($this->entries as $entry) {
            if ($entry->appliesAt() <= $when) {
                $balance = $balance->add($entry->amount());
            }
        }

        return $balance;
    }

    /**
     * @return list<Entry>
     */
    public function toList(): array
    {
        return $this->entries;
    }

    /**
     * @return list<Money>
     */
    public function amounts(): array
    {
        return array_map(
            fn(Entry $entry): Money => $entry->amount(),
            $this->entries,
        );
    }

    public function copy(): self
    {
        return new self($this->entries);
    }
}
