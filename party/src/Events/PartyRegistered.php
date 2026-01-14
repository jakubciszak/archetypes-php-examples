<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

interface PartyRegistered extends PartyRelatedEvent
{
    /**
     * @return array<string>
     */
    public function registeredIdentifiers(): array;

    /**
     * @return array<string>
     */
    public function roles(): array;
}
