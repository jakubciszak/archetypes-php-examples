<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party;

final readonly class PersonalData
{
    private const string EMPTY = '';

    public function __construct(
        private ?string $firstName,
        private ?string $lastName
    ) {
    }

    public static function from(string $firstName, string $lastName): self
    {
        return new self($firstName, $lastName);
    }

    public static function empty(): self
    {
        return new self(self::EMPTY, self::EMPTY);
    }

    public function firstName(): string
    {
        return $this->firstName ?? self::EMPTY;
    }

    public function lastName(): string
    {
        return $this->lastName ?? self::EMPTY;
    }
}
