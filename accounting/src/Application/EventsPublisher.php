<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Application;

use SoftwareArchetypes\Accounting\Events\AccountingEvent;

interface EventsPublisher
{
    public function publish(AccountingEvent $event): void;
}
