<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

interface EventPublisher
{
    /**
     * @param PartyRelatedEvent|array<PartyRelatedEvent> $events
     */
    public function publish(PartyRelatedEvent|array $events): void;
}
