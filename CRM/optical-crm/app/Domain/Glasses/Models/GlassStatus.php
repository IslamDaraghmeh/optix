<?php

namespace App\Domain\Glasses\Models;

class GlassStatus
{
    public const PENDING = 'pending';
    public const READY = 'ready';
    public const DELIVERED = 'delivered';

    public function __construct(
        public string $value
    ) {
        if (!in_array($value, [self::PENDING, self::READY, self::DELIVERED])) {
            throw new \InvalidArgumentException("Invalid glass status: {$value}");
        }
    }

    public function getDisplayName(): string
    {
        return match ($this->value) {
            self::PENDING => 'Pending',
            self::READY => 'Ready',
            self::DELIVERED => 'Delivered',
            default => $this->value,
        };
    }

    public function getBadgeClass(): string
    {
        return match ($this->value) {
            self::PENDING => 'bg-yellow-100 text-yellow-800',
            self::READY => 'bg-blue-100 text-blue-800',
            self::DELIVERED => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getDescription(): string
    {
        return match ($this->value) {
            self::PENDING => 'Order is being processed',
            self::READY => 'Glasses are ready for pickup',
            self::DELIVERED => 'Glasses have been delivered to patient',
            default => '',
        };
    }

    public static function fromString(string $status): self
    {
        return new self(strtolower($status));
    }

    public static function getAll(): array
    {
        return [
            new self(self::PENDING),
            new self(self::READY),
            new self(self::DELIVERED),
        ];
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
