<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Exceptions;

use Exception;

/**
 * Base exception for all domain-specific errors in the Accounting context.
 */
abstract class DomainException extends Exception {}
