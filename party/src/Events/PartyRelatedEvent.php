<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

interface PartyRelatedEvent
{
    public function partyId(): string;
}
