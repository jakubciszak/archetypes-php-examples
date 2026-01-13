<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Domain;

final class ResourceAvailability
{
    private Blockade $blockade;
    private int $version = 0;
    private ResourceId $resourceParentId;
    private TimeSlot $segment;

    public function __construct(
        private readonly ResourceAvailabilityId $id,
        private readonly ResourceId $resourceId,
        ResourceId|TimeSlot $resourceParentIdOrSegment,
        ?TimeSlot $segmentParam = null
    ) {
        // Handle two constructor signatures:
        // 1. (id, resourceId, segment)
        // 2. (id, resourceId, parentId, segment)
        if ($resourceParentIdOrSegment instanceof TimeSlot) {
            // Signature 1: (id, resourceId, segment)
            $this->segment = $resourceParentIdOrSegment;
            $this->resourceParentId = ResourceId::none();
        } else {
            // Signature 2: (id, resourceId, parentId, segment)
            $this->resourceParentId = $resourceParentIdOrSegment;
            $this->segment = $segmentParam ?? TimeSlot::empty();
        }

        $this->blockade = Blockade::none();
    }

    public static function withVersion(
        ResourceAvailabilityId $id,
        ResourceId $resourceId,
        ResourceId $resourceParentId,
        TimeSlot $segment,
        Blockade $blockade,
        int $version
    ): self {
        $availability = new self($id, $resourceId, $resourceParentId, $segment);
        $availability->blockade = $blockade;
        $availability->version = $version;
        return $availability;
    }

    public function id(): ResourceAvailabilityId
    {
        return $this->id;
    }

    public function resourceId(): ResourceId
    {
        return $this->resourceId;
    }

    public function resourceParentId(): ResourceId
    {
        return $this->resourceParentId;
    }

    public function segment(): TimeSlot
    {
        return $this->segment;
    }

    public function block(Owner $requester): bool
    {
        if ($this->isAvailableFor($requester)) {
            $this->blockade = Blockade::ownedBy($requester);
            return true;
        }

        return false;
    }

    public function release(Owner $requester): bool
    {
        if ($this->isAvailableFor($requester)) {
            $this->blockade = Blockade::none();
            return true;
        }

        return false;
    }

    public function disable(Owner $requester): bool
    {
        $this->blockade = Blockade::disabledBy($requester);
        return true;
    }

    public function enable(Owner $requester): bool
    {
        if ($this->blockade->canBeTakenBy($requester)) {
            $this->blockade = Blockade::none();
            return true;
        }

        return false;
    }

    public function isDisabled(): bool
    {
        return $this->blockade->disabled();
    }

    public function isDisabledBy(Owner $owner): bool
    {
        return $this->blockade->isDisabledBy($owner);
    }

    public function blockedBy(): Owner
    {
        return $this->blockade->takenBy();
    }

    public function version(): int
    {
        return $this->version;
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }

    private function isAvailableFor(Owner $requester): bool
    {
        return $this->blockade->canBeTakenBy($requester) && !$this->isDisabled();
    }
}
