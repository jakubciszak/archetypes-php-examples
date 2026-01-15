<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\SerialNumber;

/**
 * Abstract base class for serial numbers.
 * Serial numbers uniquely identify individual product instances.
 * Examples include: textual serial numbers, IMEI (for mobile devices), VIN (for vehicles).
 */
abstract readonly class SerialNumber
{
    /**
     * Returns the string representation of the serial number.
     */
    abstract public function asString(): string;

    /**
     * Returns the type of serial number (e.g., 'TEXTUAL', 'IMEI', 'VIN').
     */
    abstract public function type(): string;
}
