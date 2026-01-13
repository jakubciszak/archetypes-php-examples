<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Commands;

final readonly class Activate implements Command
{
    public const string TYPE = 'ACTIVATE';

    public function __construct(
        public string $assetId
    ) {
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
