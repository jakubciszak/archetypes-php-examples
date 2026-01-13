<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Events;

interface EventsPublisher
{
    public function publish(AccountingEvent $event): void;
}
