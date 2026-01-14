<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party;

use SoftwareArchetypes\Party\Common\Result;
use SoftwareArchetypes\Party\Common\Version;
use SoftwareArchetypes\Party\Events\PartyRegistered;
use SoftwareArchetypes\Party\Events\PartyRelatedEvent;
use SoftwareArchetypes\Party\Events\RegisteredIdentifierAdded;
use SoftwareArchetypes\Party\Events\RegisteredIdentifierAdditionSkipped;
use SoftwareArchetypes\Party\Events\RegisteredIdentifierRemoved;
use SoftwareArchetypes\Party\Events\RegisteredIdentifierRemovalSkipped;
use SoftwareArchetypes\Party\Events\RoleAdded;
use SoftwareArchetypes\Party\Events\RoleAdditionSkipped;
use SoftwareArchetypes\Party\Events\RoleRemoved;
use SoftwareArchetypes\Party\Events\RoleRemovalSkipped;

abstract class Party
{
    /**
     * @var array<PartyRelatedEvent>
     */
    private array $events = [];

    /**
     * @param array<Role> $roles
     * @param array<RegisteredIdentifier> $registeredIdentifiers
     */
    public function __construct(
        private readonly PartyId $partyId,
        private array $roles,
        private array $registeredIdentifiers,
        private Version $version
    ) {
    }

    abstract public function toPartyRegisteredEvent(): PartyRegistered;

    /**
     * @return Result<RoleAdditionSkipped, static>
     */
    public function addRole(Role $role): Result
    {
        foreach ($this->roles as $existingRole) {
            if ($existingRole == $role) {
                $this->register(RoleAdditionSkipped::dueToRoleAlreadyAssigned(
                    $this->partyId->asString(),
                    $role->asString()
                ));
                return Result::success($this);
            }
        }

        $this->roles[] = $role;
        $this->register(new RoleAdded($this->partyId->asString(), $role->asString()));

        return Result::success($this);
    }

    /**
     * @return Result<RoleRemovalSkipped, static>
     */
    public function removeRole(Role $role): Result
    {
        $found = false;
        $newRoles = [];

        foreach ($this->roles as $existingRole) {
            if ($existingRole == $role) {
                $found = true;
            } else {
                $newRoles[] = $existingRole;
            }
        }

        if (!$found) {
            $this->register(RoleRemovalSkipped::dueToRoleNotAssigned(
                $this->partyId->asString(),
                $role->asString()
            ));
            return Result::success($this);
        }

        $this->roles = $newRoles;
        $this->register(new RoleRemoved($this->partyId->asString(), $role->asString()));

        return Result::success($this);
    }

    /**
     * @return Result<RegisteredIdentifierAdditionSkipped, static>
     */
    public function addIdentifier(RegisteredIdentifier $identifier): Result
    {
        foreach ($this->registeredIdentifiers as $existingIdentifier) {
            if ($existingIdentifier->asString() === $identifier->asString()) {
                $this->register(RegisteredIdentifierAdditionSkipped::dueToIdentifierAlreadyRegistered(
                    $this->partyId->asString(),
                    $identifier->asString()
                ));
                return Result::success($this);
            }
        }

        $this->registeredIdentifiers[] = $identifier;
        $this->register(new RegisteredIdentifierAdded(
            $this->partyId->asString(),
            $identifier->asString()
        ));

        return Result::success($this);
    }

    /**
     * @return Result<RegisteredIdentifierRemovalSkipped, static>
     */
    public function removeIdentifier(RegisteredIdentifier $identifier): Result
    {
        $found = false;
        $newIdentifiers = [];

        foreach ($this->registeredIdentifiers as $existingIdentifier) {
            if ($existingIdentifier->asString() === $identifier->asString()) {
                $found = true;
            } else {
                $newIdentifiers[] = $existingIdentifier;
            }
        }

        if (!$found) {
            $this->register(RegisteredIdentifierRemovalSkipped::dueToIdentifierNotRegistered(
                $this->partyId->asString(),
                $identifier->asString()
            ));
            return Result::success($this);
        }

        $this->registeredIdentifiers = $newIdentifiers;
        $this->register(new RegisteredIdentifierRemoved(
            $this->partyId->asString(),
            $identifier->asString()
        ));

        return Result::success($this);
    }

    public function id(): PartyId
    {
        return $this->partyId;
    }

    /**
     * @return array<Role>
     */
    public function roles(): array
    {
        return $this->roles;
    }

    /**
     * @return array<RegisteredIdentifier>
     */
    public function registeredIdentifiers(): array
    {
        return $this->registeredIdentifiers;
    }

    public function version(): Version
    {
        return $this->version;
    }

    /**
     * @return array<PartyRelatedEvent>
     */
    public function events(): array
    {
        return $this->events;
    }

    protected function register(PartyRelatedEvent $event): void
    {
        $this->events[] = $event;
    }
}
