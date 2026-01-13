<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Commands;

final readonly class Lock implements Command
{
    public const string TYPE = 'LOCK';

    public function __construct(
        public string $assetId,
        public int $durationInMinutes
    ) {
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
