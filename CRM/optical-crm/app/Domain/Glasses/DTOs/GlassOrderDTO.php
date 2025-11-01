<?php

namespace App\Domain\Glasses\DTOs;

use App\Domain\Glasses\Models\GlassStatus;

class GlassOrderDTO
{
    public function __construct(
        public ?int $id,
        public int $patientId,
        public string $lensType,
        public string $frameType,
        public float $price,
        public GlassStatus $status,
        public \DateTime $createdAt,
        public \DateTime $updatedAt
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            patientId: $data['patient_id'],
            lensType: $data['lens_type'],
            frameType: $data['frame_type'],
            price: (float) $data['price'],
            status: GlassStatus::fromString($data['status']),
            createdAt: new \DateTime($data['created_at']),
            updatedAt: new \DateTime($data['updated_at'])
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patientId,
            'lens_type' => $this->lensType,
            'frame_type' => $this->frameType,
            'price' => $this->price,
            'status' => $this->status->value,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }

    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function getStatusDisplay(): string
    {
        return $this->status->getDisplayName();
    }

    public function getStatusBadgeClass(): string
    {
        return $this->status->getBadgeClass();
    }
}
