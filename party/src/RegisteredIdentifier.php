<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party;

/**
 * Interface for registered identifiers (e.g., tax numbers, passport IDs).
 * Can be enhanced with validity period in the future.
 */
interface RegisteredIdentifier
{
    public function type(): string;

    public function asString(): string;
}
