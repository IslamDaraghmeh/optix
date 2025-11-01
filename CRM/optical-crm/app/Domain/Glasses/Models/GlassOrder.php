<?php

namespace App\Domain\Glasses\Models;

class GlassOrder
{
    private int $id;
    private int $patientId;
    private string $lensType;
    private string $frameType;
    private float $price;
    private GlassStatus $status;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        int $patientId,
        string $lensType,
        string $frameType,
        float $price,
        GlassStatus $status = GlassStatus::PENDING,
        ?int $id = null,
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->patientId = $patientId;
        $this->lensType = $lensType;
        $this->frameType = $frameType;
        $this->price = $price;
        $this->status = $status;
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->updatedAt = $updatedAt ?? new \DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatientId(): int
    {
        return $this->patientId;
    }

    public function getLensType(): string
    {
        return $this->lensType;
    }

    public function getFrameType(): string
    {
        return $this->frameType;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStatus(): GlassStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    // Business logic methods
    public function updateStatus(GlassStatus $newStatus): void
    {
        $this->validateStatusTransition($this->status, $newStatus);
        $this->status = $newStatus;
        $this->updatedAt = new \DateTime();
    }

    public function updateDetails(string $lensType, string $frameType, float $price): void
    {
        $this->lensType = $lensType;
        $this->frameType = $frameType;
        $this->price = $price;
        $this->updatedAt = new \DateTime();
    }

    public function canBeEdited(): bool
    {
        return $this->status !== GlassStatus::DELIVERED;
    }

    public function canBeDeleted(): bool
    {
        return $this->status === GlassStatus::PENDING;
    }

    private function validateStatusTransition(GlassStatus $from, GlassStatus $to): void
    {
        $validTransitions = [
            GlassStatus::PENDING->value => [GlassStatus::READY, GlassStatus::DELIVERED],
            GlassStatus::READY->value => [GlassStatus::DELIVERED],
            GlassStatus::DELIVERED->value => []
        ];

        if (!in_array($to, $validTransitions[$from->value])) {
            throw new \InvalidArgumentException(
                "Invalid status transition from {$from->value} to {$to->value}"
            );
        }
    }
}
