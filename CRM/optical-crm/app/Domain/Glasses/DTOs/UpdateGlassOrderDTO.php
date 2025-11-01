<?php

namespace App\Domain\Glasses\DTOs;

use App\Domain\Glasses\Models\GlassStatus;

class UpdateGlassOrderDTO
{
    public function __construct(
        public int $patientId,
        public string $lensType,
        public string $frameType,
        public float $price,
        public GlassStatus $status
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            patientId: $data['patient_id'],
            lensType: $data['lens_type'],
            frameType: $data['frame_type'],
            price: (float) $data['price'],
            status: GlassStatus::fromString($data['status'])
        );
    }

    public function toArray(): array
    {
        return [
            'patient_id' => $this->patientId,
            'lens_type' => $this->lensType,
            'frame_type' => $this->frameType,
            'price' => $this->price,
            'status' => $this->status->value,
        ];
    }

    public function validate(): void
    {
        if ($this->patientId <= 0) {
            throw new \InvalidArgumentException('Patient ID must be a positive integer');
        }

        if (empty(trim($this->lensType))) {
            throw new \InvalidArgumentException('Lens type cannot be empty');
        }

        if (empty(trim($this->frameType))) {
            throw new \InvalidArgumentException('Frame type cannot be empty');
        }

        if ($this->price < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }
    }
}
